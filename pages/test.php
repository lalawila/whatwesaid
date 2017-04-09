<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
   <title>Basic Uploader for CKEDITOR</title>
   <script type="text/javascript">  
      function loadFile(url)  
      {  
        var parent_window = (window.parent == window) ? window.opener : window.parent ;  
        parent_window.CKEDITOR.tools.callFunction( <?php echo (int)$_GET['CKEditorFuncNum']; ?>, url, '');  
        window.close();  
      }  
   </script>  
</head>
<body>
<?php   
   define('_FILEUPLOADER_DIR', 'where-you-want-to-upload/');
   define('_FILEUPLAODER_BASE_URL', 'http://your-domaine.tld/where-are-upload-images/');
   $tmp_file = $_FILES['upload']['tmp_name'];

   $erreur = false ;
   if( is_uploaded_file($tmp_file) ) {
      $name_file = $_FILES['upload']['name'];
      if (!move_uploaded_file($tmp_file, _FILEUPLOADER_DIR.$name_file)) {
         $erreur = 'Rights error' ; 
      }
   } else { $erreur = 'Transmission error' ; }


   if ($erreur) {
      @unlink($tmp_file) ;
   } else {
      $url =  _FILEUPLAODER_BASE_URL.$name_file ;
   }
   $funcNum = $_GET['CKEditorFuncNum'] ;
   $CKEditor = $_GET['CKEditor'] ;
   $langCode = $_GET['langCode'] ;
   echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($funcNum, '$url', '$message');</script>";
?>
</body>
</html>
