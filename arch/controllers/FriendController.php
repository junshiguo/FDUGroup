<?php
class FriendController extends RController {
    public $access = array(
        Role::AUTHENTICATED => array('add', 'confirm', 'decline')
    );

    /* Add friend request */
    public function actionAdd($userId = null) {
        /* TODO */
        $currentUserId = Rays::app()->getLoginUser()->id;
        $currentUserName = Rays::app()->getLoginUser()->name;
        if ($currentUserId !== $userId) {
            $content = RHtmlHelper::linkAction('user',$currentUserName,'view',$currentUserId)." wants to be friends with you.<br/>" .
                RHtmlHelper::linkAction('friend','Confirm','confirm',$currentUserId,array('class'=>'btn btn-xs btn-success')).'&nbsp;&nbsp;'.
                RHtmlHelper::linkAction('friend','Decline','decline',$currentUserId,array('class'=>'btn btn-xs btn-danger'));
            $message = new Message();
            $message->sendMsg("system", $currentUserId, $userId, "Friend request", $content, '');

            //add friend sensor item
            $censor = new Censor();
            $censor->addFriendApplication($currentUserId, $userId);
            $this->flash('message', 'Adding friend request has been sent.');
            $this->redirectAction('user', 'view', $userId);
        }
    }

    /* Confirm friend request */
    public function actionConfirm($userId = null) {
        $currentUserId = Rays::app()->getLoginUser()->id;
        $currentUserName = Rays::app()->getLoginUser()->name;

        $friend = new Friend();
        $friend->uid = $currentUserId;
        $friend->fid = $userId;

        //only request exist can friendship be built
        $censor = new Censor();
        $cid = $censor->addFriendExist($userId, $currentUserId);

        if ($cid === null) {
            $this->flash('warning','Request already processed');
        } else {
            $censor->passCensor($cid);
            if (count($friend->find()) == 0) {     //bug fixed by songrenchu: only new relationship need to be inserted
                $friend->insert();

                $friend = new Friend();
                $friend->uid = $userId;
                $friend->fid = $currentUserId;
                $friend->insert();

                $content = RHtmlHelper::linkAction('user',$currentUserName,'view',$currentUserId)." has accepted your friend request.";

                $message = new Message();
                $message->sendMsg("system", $currentUserId, $userId, "Friend confirmed", $content, '');
                $this->flash('message','Friends confirmed.');
            }
            else{
                $this->flash('warning','You two are already friends.');
            }
        }

        $this->redirectAction('message', 'view', null);
    }

    /* Decline friend request */
    public function actionDecline($userId = null) {
        $currentUserId = Rays::app()->getLoginUser()->id;
        $currentUserName = Rays::app()->getLoginUser()->name;

        //only request exist can friendship be declined
        $censor = new Censor();
        $cid = $censor->addFriendExist($userId, $currentUserId);

        if ($cid === null) {
            $this->flash('warning','Request already processed');
        } else {
            $censor->failCensor($cid);

            $content = RHtmlHelper::linkAction('user',$currentUserName,'view',$currentUserId)." has declined your friend request.";
            $message = new Message();
            $message->sendMsg("system", $currentUserId, $userId, "Friend request declined", $content, '');
            $this->flash('message','Friend request declined.');
        }
        $this->redirectAction('message', 'view', null);
    }

    /* Cancel friend relationship */
    public function actionCancel($userId = null) {
        $currentUserId = Rays::app()->getLoginUser()->id;
        $currentUserName = Rays::app()->getLoginUser()->name;

        $friend = new Friend();
        $friend->delete(['uid' => $currentUserId, 'fid' => $userId]);

        $friend = new Friend();
        $friend->delete(['uid' => $userId, 'fid' => $currentUserId]);

        $this->redirectAction('user', 'view', $userId);
    }
}
