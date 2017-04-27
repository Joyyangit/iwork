
<?php $this->layout('layouts/plane', ['title' => 'check模版']) ?>
<?php $this->start('body') ?>

<?php
if (is_null($task)) {
    $task = (object) array();
    $task->id = 0;
    $task->title = '没有标题的标题';
    $task->department = 0;

}
?>

<h1>check模版</h1>
<hr />

<form method="POST" action="/task/addcheck">
<input type="hidden" name="id" value="<?=$task->id?>" />

<div class="row line">
  <div class="col-sm-6">

<div class="form-group">
<div class="input-group">
<span class="input-group-addon">标题</span>
<input name="row[title]" type="text" class="form-control" value="<?=$task->title?>">
</div>
</div>

  </div>

  <div class="col-sm-2">
<div class="form-group">
<div class="input-group">
<span class="input-group-addon">部门</span>
<select name="row[department]" class="form-control">
<?php $this->insert('selection-users', ['data' => $departments, 'slt' => $task->department ]) ?>
</select>
</div>
</div>

  </div>
</div>

<div class="row">
  <div class="col-lg-12" id="checklist">

<?php if ($task->id) :?>
<?php foreach (json_decode($task->content) as $i => $desc) :?>
<div class="input-group input-group-lg line">
  <span class="input-group-addon"><?=$i+1?></span>
  <textarea class="form-control" name="checklist[]"><?=$desc?></textarea>
  <span class="input-group-btn">
        <button class="btn btn-default" type="button" onclick="addcheckone(this);"> + </button>
        <button class="btn btn-default" type="button" onclick="delcheckone(this);"> X </button>
  </span>
</div>
<?php endforeach ?>
<?php else :?>
<div class="input-group input-group-lg line">
  <span class="input-group-addon">1</span>
  <textarea class="form-control" name="checklist[]"></textarea>
  <span class="input-group-btn">
        <button class="btn btn-default" type="button" onclick="addcheckone(this);"> + </button>
        <button class="btn btn-default" type="button" onclick="delcheckone(this);"> X </button>
  </span>
</div>
<?php endif ?>

  </div>
</div>

<div class="row">
    <div class="col-sm-4">
        <button type="submit" class="btn btn-danger btn-lg btn-block"> 提 交 </button>
    </div>
</div>

</form>

<div class="hide" id="checkone">
  <div class="input-group input-group-lg line">
  <span class="input-group-addon">-</span>
  <textarea class="form-control" name="checklist[]"></textarea>
  <span class="input-group-btn">
        <button class="btn btn-default" type="button" onclick="addcheckone(this);"> + </button>
        <button class="btn btn-default" type="button" onclick="delcheckone(this);"> X </button>
  </span>
  </div>
</div>

<?php $this->end() ?>

<?php $this->start('js') ?>

<script type="text/javascript">

function addcheckone( o ) {
  $(o).parent().parent().after($("#checkone").html());
  $("#checklist").children().each(function(i) {
    $(this).children("span:first").html(i + 1);
  });
}

function delcheckone( o ) {
  var list = $("#checklist").children();
  if (list.length == 1) {
    return;
  }

  $(o).parent().parent().remove();
  $("#checklist").children().each(function(i) {
    $(this).children("span:first").html(i + 1);
  });
}

</script>

<?php $this->end() ?>
