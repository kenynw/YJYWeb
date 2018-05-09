<?php

namespace backend\components;

use Yii;
use yii\helpers\Url;
use yii\db\ActiveRecord;
use yii\base\Object;
use common\models\Brand;

class AdminLog
{
    
    public static function write($event)
    {
        if($event->sender instanceof \common\models\AdminLog || !$event->sender->primaryKey() || $event->sender->tableSchema->name == 'yjy_admin_log_view') {
            return;
        }

        if ($event->name == ActiveRecord::EVENT_AFTER_INSERT) {
            $description = '新增：表《%s》<br>%s为%s<br><$>内容为（{$}%s）';
        } elseif($event->name == ActiveRecord::EVENT_AFTER_UPDATE) {
            $description = '修改：表《%s》<br>%s为%s<br><$>内容为（{$}%s）';
        } else {
            $description = '删除：表《%s》<br>%s为%s<br><$>内容为（{$}%s）';
        }
        
        $desc = '';
        $modelStr = get_class($event->sender);
        $model = new $modelStr;
        
        if (!empty($event->changedAttributes)) {
            $i = 0;
            foreach($event->changedAttributes as $name => $value) {
                $i += 1;
                if ($event->sender->$name == $value || $name == 'updated_at' || $name == 'update_time') {
                } else {
                    $nameLabel = $model->getAttributeLabel($name);
                    
                    if ($event->name == ActiveRecord::EVENT_AFTER_INSERT) {
                        $desc .= $i.'、'.$nameLabel . ' : ' . $event->sender->getAttribute($name) . '{$}';
                    } elseif($event->name == ActiveRecord::EVENT_AFTER_UPDATE) {
                        $desc .= $i.'、'.$nameLabel . ' : ' . $value . ' => ' . $event->sender->getAttribute($name) . '{$}';
                    }
                }
            }
//             $desc = substr($desc, 0, -1);

        } elseif (empty($event->changedAttributes) && ($event->name == ActiveRecord::EVENT_AFTER_DELETE)) {
            $i = 0;
            foreach($event->sender->attributes as $name => $value) {
                    $i += 1;
                    $nameLabel = $model->getAttributeLabel($name);
                    $desc .= $i.'、'.$nameLabel . ' : ' . $value . '{$}';
            }
        } else {
            $desc = '';
        }

        if (!empty($desc)) {
            $userName = Yii::$app->user->identity->username;
            $tableName = $event->sender->tableSchema->name;
            $primaryKey = is_array($event->sender->getPrimaryKey()) ? current($event->sender->getPrimaryKey()) : $event->sender->getPrimaryKey();
            $description = sprintf($description, $tableName, $event->sender->primaryKey()[0], $primaryKey, $desc);
            
            $route = Url::to();
            $userId = Yii::$app->user->id;
            $ip = ip2long(Yii::$app->request->userIP);
            
            $data = [
                'route' => $route,
                'description' => $description,
                'user_id' => $userId,
                'username' => $userName,
                'ip' => $ip,
                'created_at' => time()
            ];
            
            $model = new \common\models\AdminLog();
            $model->setAttributes($data);
            $model->save();
        }
    }
}
