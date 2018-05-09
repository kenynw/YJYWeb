<?php
namespace common\components;
use Yii;

/**  
 *   用户中心任务类     
 */
class Task{

    private  $userInfo = [];
    public function __construct($userId) {
        //查询用户资料
        $sql        = "SELECT *  FROM {{%user}}  WHERE id = '$userId'";
        $userInfo   = Yii::$app->db->createCommand($sql)->queryOne();
        $userInfo['uid']  = $userInfo['id'];
        $this->userInfo   = $userInfo;
    }
    /******************************************************************************************************************/
    /********************************新手任务区***********************************************************************/
    /*****************************************************************************************************************/
    /**  
     *   是否已注册
     */
    function register($taskid) {
        return $this->userInfo['uid'] ? true : false; 
    }

    /**  
     *   上传头像任务
    */
    function avatar($taskid) {
        return $this->userInfo['img'] != 'photo/member.png' ? true : false; 
    }

    /**  
     *   绑定手机任务
     */
    function bandmobile($taskid) {
        //查询是否领取
        return $this->userInfo['mobile'] ? true : false; 
    }
    /**  
     *   完善资料任务
     */
    function profile($taskid) {
        $musts      = array('username','mobile','birth_year','birth_month','birth_day','city');
        $num        = 0;

        //查询是否完成
        foreach ($this->userInfo as $key => $value) {
            if(in_array($key,$musts) && $value){
                $num++;
            }
        }
        return $num == 6 ? true : false;
    }
    /******************************************************************************************************************/
    /********************************每日任务区************************************************************************/
    /*****************************************************************************************************************/
    /**  
     *   每日签到任务
     */
    function paulsign($taskid) {
        $time           = time();
        $start_time     = strtotime(date('Y-m-d',$time));
        $end_time       = strtotime(date('Y-m-d',$time + 24*3600));
        $uid            = $this->userInfo['uid'];

        //查询是否完成
        $sql            = "SELECT id FROM {{%checkin}} WHERE user_id = '$uid' AND checkin_time >= '$start_time' AND checkin_time < '$end_time'";
        $return         = Yii::$app->db->createCommand($sql)->queryOne();
        return !empty($return) ? true : false; 
    }
    /**  
     *   每日发贴
     */
    function dailypost($taskid) {
        $time           = time();
        $start_time     = strtotime(date('Y-m-d',$time));
        $end_time       = strtotime(date('Y-m-d',$time + 24*3600));
        $uid            = $this->userInfo['uid'];

        //查询是否完成
        $sql            = "SELECT id FROM {{%post}} WHERE user_id = '$uid' AND created_at >= '$start_time' AND created_at < '$end_time'";
        $return         = Yii::$app->db->createCommand($sql)->queryOne();
        return !empty($return) ? true : false; 
    }
    /**  
     *   每日回帖
     */
    function replypost($taskid) {
        $time           = time();
        $start_time     = strtotime(date('Y-m-d',$time));
        $end_time       = strtotime(date('Y-m-d',$time + 24*3600));
        $uid            = $this->userInfo['uid'];

        //查询是否完成
        $sql            = "SELECT id FROM {{%comment}} WHERE user_id = '$uid' AND created_at >= '$start_time' AND created_at < '$end_time'";
        $return         = Yii::$app->db->createCommand($sql)->queryOne();
        return !empty($return) ? true : false; 
    }
    /**  
     *   每日点赞
     */
    function postlike($taskid) {
        $time           = time();
        $start_time     = strtotime(date('Y-m-d',$time));
        $end_time       = strtotime(date('Y-m-d',$time + 24*3600));
        $uid            = $this->userInfo['uid'];

        //查询是否完成
        $sql            = "SELECT id FROM {{%post_like}} WHERE user_id = '$uid' AND created_at >= '$start_time' AND created_at < '$end_time'";
        $return         = Yii::$app->db->createCommand($sql)->queryOne();
        return !empty($return) ? true : false; 
    }
    /**  
     *   每日精华
     */
    function postdigest($taskid) {
        $time           = time();
        $start_time     = strtotime(date('Y-m-d',$time));
        $end_time       = strtotime(date('Y-m-d',$time + 24*3600));
        $uid            = $this->userInfo['uid'];

        //查询是否完成
        $sql            = "SELECT id FROM {{%post}} WHERE user_id = '$uid' AND digest = '1' AND created_at >= '$start_time' AND created_at < '$end_time'";
        $return         = Yii::$app->db->createCommand($sql)->queryOne();
        return !empty($return) ? true : false; 
    }
    /**  
     *   每日参与投票
     */
    function voteoption($taskid) {
        $time           = time();
        $start_time     = strtotime(date('Y-m-d',$time));
        $end_time       = strtotime(date('Y-m-d',$time + 24*3600));
        $uid            = $this->userInfo['uid'];

        //查询是否完成
        $sql            = "SELECT id FROM {{%user_vote}} WHERE user_id = '$uid' AND created_at >= '$start_time' AND created_at < '$end_time'";
        $return         = Yii::$app->db->createCommand($sql)->queryOne();
        return !empty($return) ? true : false; 
    }
    /**  
     *   查询任务是否领取方法
     */
    // private function isReceive($taskid,$type = 0){
    //     $uid            = $this->userInfo['uid'];
    //     $time           = time();
    //     $start_time     = strtotime(date('Y-m-d',$time));
    //     $end_time       = strtotime(date('Y-m-d',$time + 24*3600));
    //     $whereStr       = " user_id = '$uid' AND taskid = '$taskid' ";
    //     $whereStr      .= $type == '1' ? " AND addtime >= '$start_time' AND addtime < '$end_time'" :'';
    //      //查询是否领取
    //     $receiveSql     = "SELECT id FROM {{%task_log}} WHERE $whereStr";
    //     $isReceive      = Yii::$app->db->createCommand($receiveSql)->queryOne();

    //     return !empty($isReceive);
    // }
}
?>