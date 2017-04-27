<?php $this->layout('layouts/plane', ['title' => $title]) ?>

<?php $this->start('body') ?>
<a name="top"></a>
<div class="line"></div>
<img src="/assets/logo.png">
<hr />

<div class="row">

<div class="col-lg-1">

<div class="list-group">
  <a href="/task/index" class="list-group-item<?=(I\Request::is('/task/index') ? ' active' : '') ?>">任务清单</a>
  <a href="/task/checks" class="list-group-item<?=(I\Request::is('/task/checks') ? ' active' : '') ?>">CHECKS</a>
  <a href="/tag/vvv" class="list-group-item<?=(I\Request::is('/tag/vvv') ? ' active' : '') ?>">维度统计</a>
</div>

<div class="list-group">
  <a target="_blank" href="http://www.chiark.greenend.org.uk/~sgtatham/bugs-cn.html" class="list-group-item">如何报告BUG</a>
  <a href="/task/ido" class="list-group-item<?=(I\Request::is('/task/ido') ? ' active' : '') ?>">我的任务</a>
  <a href="/task/icommit" class="list-group-item<?=(I\Request::is('/task/icommit') ? ' active' : '') ?>">我发布的</a>
  <a href="/task/itest" class="list-group-item <?=(I\Request::is('/task/itest') ? ' active' : '') ?>">我的验收</a>
</div>

<div class="list-group">
  <a href="/pro/index" class="list-group-item<?=(I\Request::isctl('pro') ? ' active' : '' )?>">项目管理</a>
  <a href="/tag/index" class="list-group-item<?=(!I\Request::is('/tag/vvv') && I\Request::isctl('tag') ? ' active' : '') ?>">版本管理</a>
  <a href="/title/index" class="list-group-item<?=(I\Request::isctl('title') ? ' active' : '') ?>">字段管理</a>
  <a href="/user/index" class="list-group-item<?=(!I\Request::is('/user/edit') && I\Request::isctl('user') ? ' active' : '') ?>">成员管理</a>
</div>

<div class="list-group">
<a href="/user/edit" class="list-group-item<?=(I\Request::is('/user/edit') ? ' active' : '') ?>">修改密码</a>
<a href="/auth/logout" class="list-group-item">退出</a>
</div>


</div>

<div class="col-lg-11">
<?=$this->section('main')?>
</div>

</div>

<?php $this->stop() ?>
