<?php
    $error = "";
    $base64_string = $_POST['base64_string'];
    $savename = uniqid().'.jpeg';//localResizeIMG压缩后的图片都是jpeg格式
    $savepath = '../../../upload/image/'.date("Ymd",time()).'/'; 
    if(!is_dir($savepath)) {
        // 检查目录是否编码后的
        if(is_dir(base64_decode($savepath))) {
            $savePath   =   base64_decode($savepath);
        }else{
            // 尝试创建目录
            if(!mkdir($savepath, 0777, true)){
                $error  =  '上传目录'.$savepath.'不存在';
            }
        }
    }else {
        if(!is_writeable($savepath)) {
            $error  =  '上传目录'.$savepath.'不可写';
            
        }
    }
    if($error){
        echo '{"status":0,"content":"'.$error.'"}';
        die();
    }
    $savepath .= $savename;
    $image = base64_to_img($base64_string,$savepath);
    $image = str_replace('../../..','',$image);
    if($image){
        echo '{"status":1,"content":"上传成功","url":"'.$image.'"}';
    }else{
        echo '{"status":0,"content":"上传失败"}';
    } 
    function base64_to_img( $base64_string, $output_file ) {
        $ifp = fopen( $output_file, "wb" ); 
        fwrite( $ifp, base64_decode( $base64_string) ); 
        fclose( $ifp ); 
        return( $output_file ); 
    } 
?>