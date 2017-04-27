<?php $this->layout('layouts/dashboard', ['title' => '任务列表']) ?>
<?php $this->start('main') ?>

<div class="alert alert-success">
<div class="row">
<div class="col-lg-2">
  <div class="input-group">
    <input id="gid" type="text" class="form-control" placeholder="输入编号直接打开">
    <span class="input-group-btn">
      <button onclick="window.open( '/task/show/' + $('#gid').val() );" class="btn btn-default" type="button">Go!</button>
    </span>
  </div>
</div>

<div class="col-lg-3">
  <div class="input-group">
    <input id="stitle" type="text" class="form-control" placeholder="标题模糊查询">
    <span class="input-group-btn">
      <button onclick='getlist( "title=" + $("#stitle").val() ); ' class="btn btn-default" type="button"><span class="glyphicon glyphicon-search" aria-hidden="true"></span> Search</button>
    </span>
  </div>
</div>

<div class="col-lg-1">
<?php if (I\Request::is('/task/checks')):?>
  <a href="/task/check" target="_blank" class="btn btn-primary"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> 发布CHECK </a>
<?php else :?>
  <a href="/task/create" target="_blank" class="btn btn-primary"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> 发布任务 </a>
<?php endif ?>
</div>

<div class="col-lg-6">
<form target="_blank"  class="form-inline" role="form" method="POST" action="/tag/stats">
<div class="form-group"> <div class="input-group">
<span class="input-group-addon">开始</span>
<input name="start" class="form-control" type="text" onclick="showcalendar(event, this)">
</div></div>

<div class="form-group"> <div class="input-group">
<span class="input-group-addon">结束</span>
<input name="end" class="form-control" type="text" onclick="showcalendar(event, this)">
</div></div>

<button class="btn btn-success"><span class="glyphicon glyphicon-stats" aria-hidden="true"></span> 查看统计</button>
</form>
</div>

</div>
</div>

<div class="row">
<div class="col-lg-12" id="taskfilter">
<input type="hidden" itag="val" name="search[author]" value="<?=(isset($options['author']) ? $options['author'] : 0)?>" >

<div class="form-inline">

<div class="form-group"> <div class="input-group">
<span class="input-group-addon">筛选条件：</span>
<select onchange="onFilterChangePro( this.value );taskFilter( 1 );" itag="val" name="search[pro]" class="form-control">
<option value="0">项目</option>
<?php $this->insert('selection-users', ['data' => $pros, 'slt' => isset($options['pro']) ? $options['pro'] : 0 ]) ?>
</select>
</div></div>

<div class="form-group">
<select onchange="taskFilter( 1 );" itag="val" name="search[tag]" class="form-control" id="filterTags">
<option value="0">版本</option>
<?php $this->insert('selection-users', ['data' => $tags, 'slt' => isset($options['tag']) ? $options['tag'] : 0 ]) ?>
</select>
</div>

<div class="form-group">
<select onchange="taskFilter( 1 );" itag="val" name="search[status]" class="form-control">
<option value="0">状态</option>
<?php $this->insert('selection', ['data' => $status, 'slt' => isset($options['status']) ? $options['status'] : 0 ]) ?>
</select>
</div>

<div class="form-group">
<select onchange="taskFilter( 1 );" itag="val" name="search[priority]" class="form-control">
<option value="0">优先级</option>
<?php $this->insert('selection', ['data' => $prioritys, 'slt' => isset($options['priority']) ? $options['priority'] : 0 ]) ?>
</select>
</div>

<div class="form-group">
<select onchange="taskFilter( 1 );" itag="val" name="search[caty]" class="form-control">
<option value="0">类型</option>
<?php $this->insert('selection-users', ['data' => $catys, 'slt' => isset($options['caty']) ? $options['caty'] : 0 ]) ?>
</select>
</div>


<div class="form-group">
<select onchange="onFilterChangeDepartment(this.value);taskFilter( 1 );" itag="val" name="search[department]" class="form-control">
<option value="0">部门</option>
<?php $this->insert('selection-users', ['data' => $departments, 'slt' => isset($options['departments']) ? $options['departments'] : 0 ]) ?>
</select>
</div>

<div class="form-group">
<select onchange="taskFilter( 1 );" itag="val" name="search[leader]" class="form-control" id="filterLeaders">
<option value="0">负责人</option>
<?php $this->insert('selection-users', ['data' => $users, 'slt' => isset($options['leader']) ? $options['leader'] : 0 ]) ?>
</select>
</div>

<div class="form-group">
<select onchange="taskFilter( 1 );" itag="val" name="search[tester]" class="form-control" id="filterLeaders">
<option value="0">验收人</option>
<?php $this->insert('selection-users', ['data' => $users, 'slt' => isset($options['tester']) ? $options['tester'] : 0 ]) ?>
</select>
</div>

<div class="form-group">
<select onchange="taskFilter( 1 );" itag="val" name="orderby" class="form-control" id="filterLeaders">
<option value="0">排序</option>
<?php $this->insert('selection', ['data' => ['updated_at' => '修改时间', 'deadline' => '限期时间'], 'slt' => $orderby ]) ?>
</select>
</div>

</div>
</div>
</div>
<div class="line"></div>

<table class="table table-bordered table-hover table-striped vertical-middle text-center">
  <thead>
    <tr>
        <th width="20" class="text-center">
<input type="checkbox" onclick="checkall('tasklist', 'ids[]',  $(this).prop('checked') );"></th>
  <th width="100" class="text-center"> 项目 </th>
  <th width="100" class="text-center"> 版本 </th>
  <th width="80" class="text-center"> 状态 </th>
  <th width="50" class="text-center"> 等级 </th>
  <th width="50" class="text-center"> 类型 </th>
  <th>标题 </th>
  <th width="80" class="text-center"> 部门 </th>
  <th width="80" class="text-center"> 负责人 </th>
  <th width="80" class="text-center"> 验收人 </th>
  <th width="155" class="text-center">修改时间</th>
  <th width="155" class="text-center">期限</th>
    </tr>
  </thead>
  <tbody id="tasklist">
<?php $this->insert('task-list-content', [
  'prioritys' => $prioritys,
  'tasks' => $tasks,
  'catys' => $catys,
  'departments' => $departments,
  'pros' => $pros,
  'tags' => $tags,
  'users' => $users,
  'totalnum' => $totalnum,
  'curpage' => $curpage,
  'perpage' => $perpage])
?>
  </tbody>
</table>

<div class="row">

  <div class="col-lg-12">
<div class="form-inline" id="changemoreform">
    <div class="form-group">
    <div class="input-group">
        <span class="input-group-addon">状态</span>
<select itag="val" name="changeto[status]" class="form-control">
<option value="0">不修改</option>
<?php $this->insert('selection', ['data' => $status, 'slt' => 0 ]) ?>
</select>
    </div>
    </div>

    <div class="form-group">
    <div class="input-group">
        <span class="input-group-addon">优先级</span>
<select itag="val" name="changeto[priority]" class="form-control">
<option value="0">不修改</option>
<?php $this->insert('selection', ['data' => $prioritys, 'slt' => 0 ]) ?>
</select>
    </div>
    </div>

    <div class="form-group">
    <div class="input-group">
        <span class="input-group-addon">部门</span>
<select class="form-control" onchange="onChangeDepartment( this.value )">
<option value="0">不修改</option>
<?php $this->insert('selection-users', ['data' => $departments, 'slt' => 0 ]) ?>
</select>
    </div>
    </div>

    <div class="form-group">
    <div class="input-group">
        <span class="input-group-addon">执行</span>
<select itag="val" name="changeto[leader]" class="form-control" id="leaders">
<option value="0">不修改</option>
<?php $this->insert('selection-users', ['data' => $users, 'slt' => 0 ]) ?>
</select>
    </div>
    </div>

    <div class="form-group">
    <div class="input-group">
        <span class="input-group-addon">验收</span>
<select itag="val" name="changeto[tester]" class="form-control" id="leaders">
<option value="0">不修改</option>
<?php $this->insert('selection-users', ['data' => $users, 'slt' => 0 ]) ?>
</select>
    </div>
    </div>

    <div class="form-group">
    <div class="input-group">
        <span class="input-group-addon">项目</span>
<select class="form-control" onchange="onChangePro(this.value);">
<option value="0">不修改</option>
<?php $this->insert('selection-users', ['data' => $pros, 'slt' => 0 ]) ?>
</select>
    </div>
    </div>

    <div class="form-group">
    <div class="input-group">
        <span class="input-group-addon">版本</span>
<select itag="val" name="changeto[tag]" class="form-control" id="tags">
<option value="0">不修改</option>
</select>
    </div>
    </div>

    <button onclick="changeMore( );" class="btn btn-danger">批量修改</button>

</div>

  <p></p>
  </div>

</div>

<?php $this->end() ?>

<?php $this->start('js') ?>

<script type="text/javascript">
var users = <?=json_encode($users)?>;
var tags = <?=json_encode($tags)?>;

var page = 1;
function taskFilter( _page ) {
  if (_page) {
    page = _page;
  }
  var s = "page=" + page + "&" + get_form_values( "taskfilter" );
  s += "&title=" + $("#stitle").val();
  console.log(s);
  getlist( s );
}

function getlist( s ) {
  $.ajax({
    data: s,
    type: "GET",
    url: '/task/index',
    cache: false,
    success: function( res ) {
      $("#tasklist").html( res );
    }
  });
}

setInterval( "taskFilter( );", 1000 * 60 * 5 );

function checkall( id, name, b ) {
    var els = $("#" + id + " :checkbox");
    for (var i = 0; i < els.length; i++) {
        var el = $(els[i]);
        if (name == el.prop("name")) {
            if ("undefined" == typeof(b) ) {
                if (el.prop("checked")) {
                    el.prop("checked", false);
                } else {
                    el.prop("checked", true);
                }
            } else{
                el.prop("checked", b);
            }
        }
    }
}

function changeMore( ) {
  var s = get_form_values( "tasklist" );
  if ("" == s) {
    alert( "没有选择任务" );
    return;
  }

  var changed = false;
  var els = $("#changemoreform [itag='val']");
  for (var i = 0; i < els.length; i++) {
      var el = $(els[i]);
      var va = el.val();
      if (va > 0) {
        s += "&" + el.prop("name") + "=" + va;
        el.val( 0 );
        changed = true;
      }
  }
  if (!changed) {
    alert( "没有变化" );
    return;
  }

  s += "&title=" + $("#stitle").val();
  s += "&page=" + page + "&" + get_form_values( "taskfilter" );
  console.log( s );

  $.ajax({
    data: s,
    url:'/task/index'
  }).done(function(data){
      $("#tasklist").html( data );
  });
}

</script>

<?php $this->end() ?>
