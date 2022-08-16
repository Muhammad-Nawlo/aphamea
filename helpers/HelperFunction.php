<?php

namespace backend\helpers;

use yii\helpers\FileHelper;
use yii\helpers\Url;

class HelperFunction
{
    static public function checkEmptyData($data)
    {
        foreach ($data as $d) {
            if (empty($d)) {
                return ["status" => "error", "details" => "There are missing params ($d)"];
            }
        }
    }

    static public function createFolderIfNotExist($path)
    {
        if (!is_dir(Url::to($path)))
            FileHelper::createDirectory(Url::to($path));
    }
}
