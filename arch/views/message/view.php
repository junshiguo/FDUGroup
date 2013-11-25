<?php
/**
 * User: Raysmond
 */
?>

<h2><?php echo count($msgs); ?> messages <?php echo ($type=='all'?"":$type); ?></h2>
<div class="navbar-left">
    <?php
    echo RFormHelper::openForm('message/send/user');
    echo RFormHelper::input(array('type'=>'hidden','name'=>'new','value'=>'true'));
    echo RFormHelper::input(array('type'=>'submit','value'=>'+ Write a message','class'=>'btn btn-sm btn-info'));
    echo RFormHelper::endForm();
    ?>
</div>

<div class="navbar-right">

<?php echo RHtmlHelper::linkAction('message',"All messages",'view','all',array('class'=>'btn btn-sm btn-primary'));?>

<?php echo RHtmlHelper::linkAction('message',"Unread messages",'view','unread',array('class'=>'btn btn-sm btn-success'));?>

<?php echo RHtmlHelper::linkAction('message',"Read messages",'view','read',array('class'=>'btn btn-sm btn-default'));?>

<?php //echo RHtmlHelper::linkAction('message',"Outbox",'view','send',array('class'=>'btn btn-sm btn-default'));?>

<?php echo RHtmlHelper::linkAction('message',"Trash",'view','trash',array('class'=>'btn btn-sm btn-danger'));?>

</div>
<div class="clearfix" style="margin-bottom: 10px;"></div>
<?php
    foreach($msgs as $msg)
    {
        ?>
        <div class="panel panel-info"><div class="panel-heading">
        <div style="float:right;margin-top: -2px;">
        <?php
        if($msg->receiverId==Rays::app()->getLoginUser()->id){
            if($msg->status==Message::$STATUS_UNREAD) echo RHtmlHelper::linkAction('message',"Mark read",'read',$msg->id,array('class'=>'btn btn-xs btn-success'));
            echo '&nbsp;&nbsp;';
            if($msg->status!=Message::$STATUS_TRASH) echo RHtmlHelper::linkAction('message',"Mark trash",'trash',$msg->id,array('class'=>'btn btn-xs btn-danger'));
            if($type=='trash') echo RHtmlHelper::linkAction('message',"Delete",'delete',$msg->id,array('class'=>'btn btn-xs btn-danger'));
        }
        ?>
        </div>
        <h3 class="panel-title">
        <?php
        $title =  (isset($msg->title)&&$msg->title!='')?$msg->title:"Untitled";
        echo RHtmlHelper::linkAction('message',$title,'detail',$msg->id);
        echo '</h3>';
        echo '</div><div class="panel-body">';
        $msg->load();
        echo '<div class="message-meta">';
        if($msg->sender=='system'){
            echo "From: 系统消息";
        }
        else{
            //print_r($msg);
            $msg->sender->load();
            if($msg->sender instanceof User){
                echo "From: ".RHtmlHelper::linkAction('user',$msg->sender->name,'view',$msg->sender->id);
            }
            else if($msg->sender instanceof Group){
                echo "From: ".RHtmlHelper::linkAction('group',$msg->sender->name,'detail',$msg->sender->id);
            }
        }
        echo '&nbsp;&nbsp;Delivery time: '.$msg->sendTime;
        echo '&nbsp;&nbsp;Status: '.($msg->status==1?"unread":"read");
        echo '</div>';
        echo '<p>'.RHtmlHelper::decode($msg->content).'</p>';

        echo '</div></div>';
    }
?>
