<?php
/*
 * 图片上传类
 */
class UploadImg{
	
	protected $filename;
	protected $fileInfo;
	protected $imgFlag;
	protected $maxSize;
	protected $allowMime;
	protected $allowExt;
	protected $uploadPath;
	protected $error;
	protected $ext;
	protected $uniqNameFlag;

	public function __construct($filename, $uploadPath)
	{
		$this->filename = $_FILES[$filename]['name'];
		$this->fileInfo = $_FILES[$filename];
		$this->uploadPath = $uploadPath;
		$this->imgFlag = true;
		$this->maxSize = 1*1024*1024;
		$this->allowMime = array('image/jpeg', 'image/png', 'image/gif');
		$this->allowExt = array('jpeg', 'jpg', 'png', 'gif');
		$this->ext = '';
		$this->error = '';
		$this->uniqNameFlag = false;
	}

	public function setImgFlag($imgFlag)
	{
		$this->imgFlag = $imgFlag;
	}
	
	public function setMaxSize($maxSize)
	{
		$this->maxSize = $maxSize;
	}
	
	public function setAllowExt(Array $allowExt)
	{
		$this->allowExt = $allowExt;
	}
	
	public function setUploadPath($uploadPath)
	{
		$this->uploadPath = $uploadPath;
	}
	
	public function setFileInfo($fileInfo)
	{
		$this->fileInfo = $fileInfo;
	}
	
	public function setUniqNameFlag($flag)
	{
		$this->uniqNameFlag = $flag;
	}
	
	protected function checkError()
	{
		if($this->fileInfo['error'] != 0){
			switch ($this->fileInfo['error']) {
				case 1:
					$this->error = '超过了PHP配置文件中upload_max_filesize选项的值';
					break;
				case 2:
					$this->error = '超过了表单中MAX_FILE_SIZE设置的值';
					break;
				case 3:
					$this->error = '文件部分被上传';
					break;
				case 4:
					$this->error = '没有选择上传文件';
					break;
				case 6:
					$this->error = '没有找到临时目录';
					break;
				case 7:
					$this->error = '文件不可写';
					break;
				case 8:
					$this->error = '由于php的扩展程序中断文件上传';
					break;
				default:
					$this->error = '未知错误';
					break;
			}
			return false;
		}
		return true;
	}
	
	protected function checkSize()
	{
		if($this->fileInfo['size'] > $this->maxSize){
			$this->error = '文件过大';
			return false;
		}
		return true;
	}
	
	protected function checkExt()
	{
		$this->ext = strtolower(pathinfo($this->fileInfo['name'], PATHINFO_EXTENSION));
		if(!in_array($this->ext, $this->allowExt)){
			$this->error = '不允许的后缀名';
			return false;
		}
		return true;
	}
	
	protected function checkMime()
	{
		if($this->imgFlag){
			if(!in_array($this->fileInfo['type'], $this->allowMime)){
				$this->error = '不允许的文件类型';
				return false;
			}
		}
		return true;
	}
	
	protected function checkIsImg()
	{
		if(!@getimagesize($this->fileInfo['tmp_name'])){
			$this->error = '不是图片类型';
			return false;
		}
		return true;
	}
	
	protected function checkHttpPost()
	{
		if(!is_uploaded_file($this->fileInfo['tmp_name'])){
			$this->error = '文件不是http post上传的！';
			return false;
		}
		return true;
	}
	
	protected function checkUploadPath()
	{
		if(!file_exists($this->uploadPath)){
			$this->error = '上传目录不存在！';
			return false;
		}
		return true;
	}
	
	protected function createUniqFilename()
	{
		return uniqid(microtime(true), true);
	}
	
	public function upload()
	{
		if($this->checkError() && $this->checkExt() && $this->checkSize()
			&& $this->checkIsImg() && $this->checkMime() && $this->checkHttpPost()){
			
			if($this->checkUploadPath()){
				if($this->uniqNameFlag){
					$this->filename = $this->createUniqFilename().".".$this->ext;
				}
				if(move_uploaded_file($this->fileInfo['tmp_name'], $this->uploadPath.'/'.$this->filename)){
					return $this->filename;
				}
			}else{
				return $this->error;
			}
		}else{
			return $this->error;
		}
	}
}
