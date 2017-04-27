<?php
use I\DB;
use I\View;
use I\Request;
use I\AuthedRequest;
use I\Setting;

class Controller extends AuthedRequest {
    public function index( ) {
        return $this->formatlist([]);
    }

    public function ido() {
        return $this->formatlist(['leader' => Request::$auth->id]);
    }

    public function checks() {
        return $this->formatlist(['caty' => Setting::get('worktime', 'check')]);
    }

    public function icommit() {
        return $this->formatlist(['author' => Request::$auth->id]);
    }

    public function itest() {
        return $this->formatlist( ['tester' => Request::$auth->id]);
    }

    private function formatlist($searchargs)
    {
        $db = DB::write();
        $ids = getgpc('ids');
        if ($ids) {
            $updates = array();
            foreach (getgpc('changeto') as $key => $value) {
                if ($value > 0) {
                    $updates[$key] = $value;
                }
            }

            if (isset($updates['tag'])) {
                $tag = $db->row('select * from tags where id = ' . $updates['tag']);
                $updates['pro'] = $tag->pro;
            }
            if (isset($updates['leader'])) {
                $leader = $db->row('select * from users where id = ' . $updates['leader']);
                $updates['department'] = $leader->department;
            }

            if ($updates) {
                $updates['updated_at'] = date('Y-m-d H:i:s');
                $db->update('tasks')->cols($updates)->where('ID in('.implode(',', $ids).')')->query();
            }
        }

        $options = array();
        $search = getgpc('search');
        if (!$search) {
            $search = array();
        }

        $search = array_merge($search, $searchargs);

        $where = array();
        foreach ($search as $key => $value) {
            if ($value > 0) {
                $options[$key] = $value;
                $where[] = $key . '="' . $value . '"';
            }
        }

        $title = getgpc('title');
        if ($title) {
            $options['title'] = $title;
            $where[] = 'title like "%' . $title . '%"';
        }

        $sqlcount = 'select count(*) as num from tasks';
        $sql = 'select * from tasks';
        if (count($where)) {
            $wheresql = implode(' and ', $where);
            $sqlcount .= ' where ' . $wheresql;
            $sql .= ' where ' . $wheresql;
        }

        $count_row = $db->row($sqlcount);
        $totalnum = $count_row->num;
        $curpage = getgpc( 'page', 1 );
        $perpage = 20;
        $offset = $this->page_get_start($curpage, $perpage, $totalnum);

        $sql .= ' order by status';
        $orderby = getgpc('orderby');
        if ($orderby) {
            if ('updated_at' == $orderby) {
                $sql .= ', updated_at desc';
            } elseif ('deadline' == $orderby) {
                $sql .= ', deadline';
            }
        } else {
            $orderby = '';
        }
        $sql .= ', tag desc';
        $sql .= ', priority desc';

        $sql .= " limit $perpage offset $offset";
        $tasks = $db->query($sql);

        $tpl = 'task-list';
        if (Request::isajax()) {
            $tpl = 'task-list-content';
        }
        return View::render($tpl, [
            'tasks' => $tasks,
            'pros' => DB::keyBy( "select * from pros" ),
            'users' => DB::keyBy( "select id, name, department from users" ),
            'tags' => DB::keyBy( "select id, name, pro from tags order by id desc" ),
            'status' => Setting::get('worktime', 'status'),
            'prioritys' => Setting::get('worktime', 'priority'),
            'catys' => DB::keyBy( "select * from titles where caty = " . Setting::get('worktime', 'caty') ),
            'departments' => DB::keyBy( "select * from titles where caty = " . Setting::get('worktime', 'department') ),
            'options' => $options,
            'orderby' => $orderby,
            'totalnum' => $totalnum,
            'curpage' => $curpage,
            'perpage' => $perpage
        ]);
    }

    private function page_get_start($page, $ppp, $totalnum) {
        $totalpage = ceil($totalnum / $ppp);
        $page =  max(1, min($totalpage, intval($page)));
        return ($page - 1) * $ppp;
    }

    public function create( $id = 0 ) {
        if ($id) {
            $task = DB::write()->row("SELECT * FROM `tasks` WHERE id='$id'");
        } else {
            $task = NULL;
        }
        return View::render('task-commit', [
            'task' => $task,
            'pros' => DB::keyBy( "select * from pros" ),
            'users' => DB::keyBy( "select id, name, department from users" ),
            'tags' => DB::keyBy( "select id, name, pro from tags order by id desc" ),
            'status' => Setting::get('worktime', 'status'),
            'catys' => DB::keyBy( "select * from titles where caty = " . Setting::get('worktime', 'caty') ),
            'departments' => DB::keyBy( "select * from titles where caty = " . Setting::get('worktime', 'department') ),
        ]);
    }

    public function store( ) {
        $id = getgpc('id');
        $row = getgpc('row');

        $me = Request::$auth;

        $db = DB::write();

        //check类型的类型不能更改
        $donotchange = [Setting::get( 'worktime', 'check' ), Setting::get( 'worktime', 'icheck' )];
        if ($id) {
            $task = DB::find('tasks', $id);
            if (in_array($task->caty, $donotchange) && $task->caty != $row['caty']) {
                return View::render('error', ['msg' => 'check类型的类型不能更改']);
            }
        } else {
            if (in_array($row['caty'], $donotchange)) {
                return View::render('error', ['msg' => 'check类型的类型不能更改']);
            }
        }

        $now = date('Y-m-d H:i:s');
        $row['deadline'] = strtotime($row['deadline']);
        $row['changer'] = $me->id;

        if ($id) {
            foreach ($row as $k => $v) {
                if ($task->$k == $v) {
                    unset($row[$k]);
                }
            }

            if (empty($row)) {
                return $this->onChanged( $id );
            }
        } else {
            $row['author'] = $me->id;
            $row['status'] = 12;
            $row['created_at'] = $now;
        }

        $row['updated_at'] = $now;
        $this->onChange( $row );

        if ($id) {
            if (!in_array($task->caty, $donotchange)) {
                $this->addlog($task, $row);
            }
            $db->update('tasks')->cols($row)->where('id='.$id)->query();
        } else {
            $id = $db->insert('tasks')->cols($row)->query();
        }

        return $this->onChanged( $id );
    }

    private function onChanged( $id ) {
        if (Request::isajax()) {
            return Request::response('');
        } else {
            return Request::redirect( '/task/show/' . $id );
        }
    }

    private function addlog( $old, $update ) {
        $monitor = array(
            'title', 'content', 'caty',
            'priority', 'department', 'status',
            'tag', 'pro', 'deadline',
            'changer', 'leader', 'tester'
        );
        $changed = array( );
        foreach ($monitor as $col) {
            if (isset($update[$col]) && $update[$col] != $old->$col) {
                $changed[$col] = $old->$col;
            }
        }

        if (empty($changed)) {
            return;
        }

        if (isset($changed['caty'])) {
            $row = DB::find('titles', $changed['caty']);
            $changed['caty'] = $row->name;
        }
        if (isset($changed['department'])) {
            $row = DB::find('titles', $changed['department']);
            $changed['department'] = $row->name;
        }

        if (isset($changed['priority'])) {
            $changed['priority'] = Setting::get('worktime', 'priority')[$changed['priority']];
        }
        if (isset($changed['status'])) {
            $changed['status'] = Setting::get('worktime', 'status')[$changed['status']];
        }

        if (isset($changed['tag'])) {
            $row = DB::find('tags', $changed['tag']);
            $changed['tag'] = $row->name;
        }

        if (isset($changed['pro'])) {
            $row = DB::find('pros', $changed['pro']);
            $changed['pro'] = $row->name;
        }

        foreach (['author', 'leader', 'tester', 'changer'] as $col) {
            if (isset($changed[$col])) {
                $row = DB::find('users', $changed[$col]);
                $changed[$col] = $row->name;
            }
        }

        $changed['pid'] = $old->id;
        $changed['changer'] = Request::$auth->name;
        $changed['created_at'] = strtotime($old->updated_at);
        DB::write()->insert('tasklogs')->cols($changed)->query();
    }

    private function onChange( &$row ) {
        if (isset($row['tag'])) {
            $tag = DB::find('tags', $row['tag']);
            $row['pro'] = $tag->pro;
        }

        if (isset($row['leader'])) {
            $leader = DB::find('users', $row['leader']);
            $row['department'] = $leader->department;
        }
    }

    public function show($id) {
        $db = DB::write();
        $task = $db->row('select * from tasks where id=' . $id);

        $tpl = 'task-show';
        if ($task->caty == Setting::get('worktime', 'check')) {
            $tpl = 'check-show';
        } elseif ($task->caty == Setting::get('worktime', 'icheck')) {
            $tpl = 'task-check';
        }

        return View::render($tpl, [
            'task' => $task,
            'feedbacks' => $db->query("select * from feedbacks where pid=$id"),
            'logs' => $db->query("select * from tasklogs where pid=$id"),
            'users' => DB::keyBy( "select id, name, department from users" ),
            'pros' => DB::keyBy('select * from pros'),
            'tags' => DB::keyBy('select id, name, pro from tags'),
            'catys' => DB::keyBy( "select * from titles where caty = " . Setting::get('worktime', 'caty') ),
            'departments' => DB::keyBy( "select * from titles where caty = " . Setting::get('worktime', 'department') ),
        ]);
    }

    public function content( $id ) {
        Request::response( DB::find('tasks', $id)->content );
    }

    public function upload( ) {
        $a = array( 'err' => 'do not recive file.' );

        $filedir = '/upload/'.date('Ym');
        $uploaddir = PUBLIC_DIR . $filedir;
        if (!is_dir($uploaddir)) {
            mkdir($uploaddir);
        }

        if (!isset($_FILES['file'])) { return ; }
        $file = $_FILES['file'];

        if (0 == $file['size']) {
            return Request::json( $a );
        }

        $filename = time() . '_' . rand( 100, 999 );
        $uploadfile = $uploaddir . '/' . $filename;
        $filepath = $filedir . '/' . $filename;
        if (!file_exists($uploadfile)) {
            move_uploaded_file($file['tmp_name'], $uploadfile);
        }

        $a['path'] = $filepath;

        return Request::json( $a );
    }

    public function check( $id = 0 ) {

        $db = DB::write();
        if ($id > 0) {
            $task = $db->row('select * from tasks where id = ' . $id);
        } else {
            $task = NULL;
        }

        return View::render('check-commit', [
            'task' => $task,
            'departments' => DB::keyBy( "select * from titles where caty = " . Setting::get('worktime', 'department') ),
            ]);
    }

    public function addcheck( ) {
        $id = getgpc('id');
        $row = getgpc('row');

        $checklist = getgpc('checklist');
        $row['content'] = json_encode($checklist, JSON_UNESCAPED_UNICODE);

        $me = Request::$auth;

        $now = date('Y-m-d H:i:s');
        $row['updated_at'] = $now;

        if ($id) {
            $db->update('tasks')->cols($row)->where('id='.$id)->query();
        } else {
            $row['author'] = $me->id;
            $row['leader'] = $me->id;
            $row['status'] = 12;
            $row['priority'] = 10;
            $row['caty'] = Setting::get( 'worktime', 'check' );
            $row['created_at'] = $now;

            $id = $db->insert('tasks')->cols($row)->query();
        }

        return Request::redirect( '/task/show/' . $id );
    }

    public function publishcheck( ) {
        $id = getgpc('id');

        $db = DB::write();
        $task = $db->row("select content from tasks where id = $id");

        $row = getgpc('row');

        $me = Request::$auth;
        $row['author'] = $me->id;

        $row['status'] = 12;
        $row['caty'] = Setting::get( 'worktime', 'icheck' );

        $row['deadline'] = strtotime($row['deadline']);

        $now = date('Y-m-d H:i:s');
        $row['updated_at'] = $now;
        $row['created_at'] = $now;

        $ccc = [];
        foreach (json_decode($task->content) as $key => $value) {
            $ccc[] = [$value, 0];
        }
        $row['content'] = json_encode($ccc, JSON_UNESCAPED_UNICODE);

        $this->onChange( $row );

        $newid = $db->insert('tasks')->cols($row)->query();

        return Request::redirect( '/task/show/' . $newid );
    }

    public function icheck( ) {
        $db = DB::write();

        $id = getgpc('id');
        $task = $db->row("select * from tasks where id = $id");

        $iid = getgpc('iid');
        $passk = getgpc('passk');

        $ccc = json_decode($task->content);
        if (!isset($ccc[$iid])) {
            Request::response( 'error');
        }

        $ccc[$iid][1] = $passk;

        $row = array();
        $row['content'] = json_encode($ccc, JSON_UNESCAPED_UNICODE);

        $now = date('Y-m-d H:i:s');
        $row['updated_at'] = $now;
        $db->update('tasks')->cols($row)->where('id='.$id)->query();

        Request::response( 'ok');
    }

    public function resetcheck($id) {
        $db = DB::write();

        $id = getgpc('id');
        $task = $db->row("select * from task where id = $id");

        $ccc = [];
        foreach (json_decode($task->content) as $key => $value) {
            $ccc[] = [$value, 0];
        }

        $row = array();
        $row['content'] = json_encode($ccc, JSON_UNESCAPED_UNICODE);

        $now = date('Y-m-d H:i:s');
        $row['updated_at'] = $now;

        $db->update('tasks')->cols($row)->where('id='.$id)->query();

        return Request::redirect( '/task/show/' . $task->id );
    }

    public function diff( $table, $oldid, $newid, $islog = 0 ) {

        if ($islog) {
            $old = DB::find( $table . 'logs', $oldid );
            $new = DB::find( $table . 'logs', $newid );
        } else {
            $old = DB::find( $table . 'logs', $oldid );
            $new = DB::find( $table . 's', $newid );
        }


        if ('task' == $table) {
            $diff = new I\HtmlDiff( $old->content, $new->content );
        } else {
            $diff = new I\HtmlDiff( $old->message, $new->message );
        }
        $diff->build();
        // echo "<h2>Old html</h2>";
        // echo $diff->getOldHtml();
        // echo "<h2>New html</h2>";
        // echo $diff->getNewHtml();
        // echo "<h2>Compared html</h2>";
        // echo $diff->getDifference();

        return Request::response( $diff->getDifference() );
        return View::render('diff', ['diff' => $diff, 'old' => $old, 'new' => $new]);
    }
}
