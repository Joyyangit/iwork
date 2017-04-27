<?php $this->layout('layouts/plane', ['title' => $task->title]) ?>
<?php $this->start('body') ?>


<div class="line"></div>
<div class="panel panel-default">
  <div class="panel-heading">
  <h1 id="title">#<?=$task->id?> <?=$task->title?></h1>
    </div>
  <div class="panel-body">

<div class="row line">
<form method="POST" action="/task/publishcheck" onsubmit="return onpublishcheck( );">
<input type="hidden" name="id" value="<?=$task->id?>" />
<div class="col-lg-12">

<div class="form-group">
<div class="input-group">
<span class="input-group-addon">标题</span>
<input id="task-title" name="row[title]" type="text" class="form-control" value="">
</div>
</div>

  <div class="line"></div>
<div class="form-inline">

<div class="input-group">
<span class="input-group-addon">部门</span>
<select class="form-control" onchange="onChangeDepartment( this.value )">
<option value="0">选择部门</option>
<?php $this->insert('selection-users', ['data' => $departments, 'slt' => 0 ]) ?>
</select>
</div>

<div class="input-group">
<span class="input-group-addon">负责人</span>
<select itag="val" name="row[leader]" class="form-control" id="leaders">
<option value="0">未选部门</option>
</select>
</div>

<div class="input-group">
<span class="input-group-addon">优先级</span>
<select itag="val" name="row[priority]" class="form-control">
<?php $this->insert('selection', ['data' => I\Setting::get('worktime', 'priority'), 'slt' => 0 ]) ?>
</select>
</div>

<div class="input-group">
<span class="input-group-addon">项目</span>
<select class="form-control" onchange="onChangePro(this.value);">
<option value="0">选择项目</option>
<?php $this->insert('selection-users', ['data' => $pros, 'slt' => 0 ]) ?>
</select>
</div>

<div class="input-group">
<span class="input-group-addon">版本</span>
<select itag="val" name="row[tag]" class="form-control" id="tags">
<option value="0">选择版本</option>
</select>
</div>

<div class="input-group">
<span class="input-group-addon">状态</span>
<select itag="val" name="row[status]" class="form-control">
<?php $this->insert('selection', ['data' => I\Setting::get('worktime', 'status'), 'slt' => $task->status ]) ?>
</select>
</div>

<div class="input-group">
<span class="input-group-addon">限期</span>
<input readonly onclick="showcalendar(event, this, true)" itag="val" name="row[deadline]" type="text" class="form-control" value="<?=date('Y-m-d H:i:s', $task->deadline ? $task->deadline : time()) ?>">
</div>

    <button type="submit" class="btn btn-danger margin-right">发布成任务</button>
    <a href="/task/check/<?=$task->id?>" class="btn btn-primary margin-right">重新编辑</a>
  </div>

</div>

</form>
</div>

  </div>
</div>

<table class="table table-striped vertical-middle text-center">
  <tbody>
<?php foreach (json_decode($task->content) as $i => $desc):?>
<tr>
  <td class="bg-primary"><?=$i+1?></td>
<td class="text-left"><pre class="ipre">
<?=$desc?>
</pre> </td>
</tr>
<?php endforeach?>
  </tbody>
</table>

<?php $this->end() ?>

<?php $this->start('js') ?>

<script type="text/javascript">
var users = <?=json_encode($users)?>;
var tags = <?=json_encode($tags)?>;

function oncommit( ) {
  if ($("#leaders").val() <= 0) {
    alert('没有选择部门或者负责人');
    return false;
  }

  if ($("#tags").val() <= 0) {
    alert('没有选择项目或者版本');
    return false;
  }

  return true;
}
</script>
<?php $this->end() ?>
