<?php
/**
 * Created by PhpStorm.
 * User: songrenchu
 */
?>

<div class="panel panel-default">
    <?=RFormHelper::openForm('ads/admin',array('id'=>'blockAdsForm'))?>
    <!-- Default panel contents -->
    <div class="panel-heading">
        Users
        <div class="navbar-right">
            <div class="input-group" style="margin-top: -6px;">
                <?=RFormHelper::input(array('name'=>'search','class'=>'form-control','style'=>'width:200px;','placeholder'=>'filter advertisements','value'=>(isset($filterStr)?$filterStr:"")))?>
                <div style="float:right; position: relative;">
                    <button class="btn btn-default" type="submit">Go!</button>
                    &nbsp;&nbsp;
                    <a class='btn btn-xs btn-danger' href="javascript:block_submit()">Block</a>
                    &nbsp;&nbsp;
                    <a class='btn btn-xs btn-success' href="javascript:active_submit()">Activate</a>
                    <?=RFormHelper::hidden(array('id'=>'operation_type','name'=>'operation_type','value'=>'block'))?>
                </div>
            </div>
        </div>
    </div>
    <!-- Table -->

    <table id="admin-ads" class="table">
        <thead>
        <tr>
            <th><input id="check-all" name="check-all" onclick="javascript:checkReverse('checked_ads[]');" type="checkbox" /></th>
            <th>Publisher</th>
            <th>Publish Time</th>
            <th>Paid price</th>
            <th>Title</th>
            <th style="width:50%;">Content</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        <?php
            foreach ($ads as $ad) {
        ?>
            <tr>
                <td>
                    <?php
                        if ($ad->status != Ads::REMOVED) {
                    ?>
                    <?=RFormHelper::input(array('name'=>'checked_ads[]', 'type'=>'checkbox','value'=>$ad->id))?>
                    <?php
                        } else echo "<span class='glyphicon glyphicon-ban-circle' style='color:red;'></spam>";
                    ?>
                </td>
                <td><?=RHtmlHelper::linkAction('user', $ad->publisher->name, 'view', $ad->publisher->id);?></td>
                <td><?=$ad->pubTime?></td>
                <td><?=$ad->paidPrice?></td>
                <td><?=$ad->title?></td>
                <td><?=RHtmlHelper::decode($ad->content)?></td>
                <td>
                    <?php
                    if ($ad->status == Ads::APPROVED) echo '<span style="color:green">active</span>';
                    else if ($ad->status == Ads::BLOCKED) echo '<span style="color:red">blocked</span>';
                    else if ($ad->status == Ads::APPLYING) echo '<span>applying</span>';
                    else if ($ad->status == Ads::REMOVED) echo '<span style="color:#ccc">removed</span>';
                    ?>
                </td>

           </tr>
        <?php
            }
        ?>
        </tbody>
    </table>
    <?=RFormHelper::endForm();?>
</div>

<?= (isset($pager) ? $pager : '') ?>
<script>
    function block_submit() {
        $("#operation_type").val('block');
        $('#blockAdsForm').submit();
    }

    function active_submit() {
        $("#operation_type").val('active');
        $('#blockAdsForm').submit();
    }

</script>