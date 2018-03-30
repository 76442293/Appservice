<?php
/**
 * FileUpLoadAction.class.php
 * 文件上传相关接口
 * DaMingGe 2018-03-28
 */
import("@.Action.BaseAction");

class FileUpLoadAction extends BaseAction
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 文件上传
     */
    public function upLoadFile()
    {

        header('Access-Control-Allow-Origin:*');
        $error = "";
        $msg = "";
        $fileElementName = "fileToUpload";

        if (!empty($_FILES[$fileElementName]['error'])) {
            switch ($_FILES[$fileElementName]['error']) {
                case '1':
                    $error = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
                    break;
                case '2':
                    $error = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
                    break;
                case '3':
                    $error = 'The uploaded file was only partially uploaded';
                    break;
                case '4':
                    $error = 'No file was uploaded.';
                    break;

                case '6':
                    $error = 'Missing a temporary folder';
                    break;
                case '7':
                    $error = 'Failed to write file to disk';
                    break;
                case '8':
                    $error = 'File upload stopped by extension';
                    break;
                case '999':
                default:
                    $error = 'No error code avaiable';
            }
        } elseif (empty($_FILES['fileToUpload']['tmp_name']) || $_FILES['fileToUpload']['tmp_name'] == 'none') {
            $error = 'No file was uploaded..';
        } else {

            $re = $this->up();

            if (!$re) {
                $error = 'Up file fail';
            }
            $msg = $re['savename'];    //文件名
            $path = '/Uploads/' . $msg;    //文件路径
            $size = $re['size'];    //文件大小
        }

        echo json_encode(array('error' => $error, 'msg' => $msg, 'path' => $path, 'size' => $size));
        exit;
    }

    private function up()
    {
        //引入UploadFile类
        import('ORG.Net.UploadFile');
        $upload = new UploadFile();

        $upload->maxSize = '-1';//默认为-1，不限制上传大小
        $upload->savePath = 'Uploads/';//保存路径
        $upload->rootPath = './';//保存路径
        $upload->saveRule = 'uniqid';//上传文件的文件名保存规则
        $upload->uploadReplace = true;//如果存在同名文件是否进行覆盖
        $upload->autoCheck = false;
        $upload->autoSub = false;
        $upload->allowExts = array('jpg', 'jpeg', 'png', 'gif', 'txt');//准许上传的文件类型
        $upload_return = $upload->upload();
        if ($upload_return) {

            $info = $upload->getUploadFileInfo();

            return $info[0];
        } else {

            return false;
            exit;
        }
    }


    function fileUpLoad()
    {
        header('Access-Control-Allow-Origin: *');
        $filetype = $_FILES["file"]["type"];
        $filename = time();
        $filepath = "Uploads/";
        if ($filetype == "image/png") {
            $filetype = ".png";
        } else if ($filetype == "image/jpeg") {
            $filetype = ".jpg";
        }
        $file_save_name = $filepath . $filename . $filetype;
        $_r = move_uploaded_file($_FILES["file"]["tmp_name"], $file_save_name);

        echo json_encode($_r, JSON_UNESCAPED_UNICODE);

    }

}