<?php
require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/InitDBAndSMTP.php");
require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/SignInManager.php");
require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/Orders.php");
if(!isset($_REQUEST["viewOrderKey"]))
	$SignInManager->Authorize(array("ADMINISTRATOR", "BROWSE"));

if(isset($_REQUEST["id"]))
{
	$order = $OrdersContext->GetOrder($_REQUEST["id"]);
	$files = $OrdersContext->GetFiles($order->OrderId);
	
	if(isset($_REQUEST["viewOrderKey"]) && $order->ViewOrderKey != $_REQUEST["viewOrderKey"])
	{
		$files = array();
		$order = new Order();
	}
}
else
{
	$files = array();
	$order = new Order();
}

if($order->OrderId == 0)
{
	require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/header.php");
	print("Order not found.");
	require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/footer.php");
}
else
{
	// https://stackoverflow.com/a/7591130
	function Download($path, $filename, $content_type, $content_disposition = "attachment", $speed = null, $multipart = true)
	{
		while (ob_get_level() > 0)
		{
			ob_end_clean();
		}

		if (is_file($path = realpath($path)) === true)
		{
			$file = @fopen($path, 'rb');
			$size = sprintf('%u', filesize($path));
			$speed = (empty($speed) === true) ? 1024 : floatval($speed);

			if (is_resource($file) === true)
			{
				set_time_limit(0);

				if (strlen(session_id()) > 0)
				{
					session_write_close();
				}

				if ($multipart === true)
				{
					$range = array(0, $size - 1);

					if (array_key_exists('HTTP_RANGE', $_SERVER) === true)
					{
						$range = array_map('intval', explode('-', preg_replace('~.*=([^,]*).*~', '$1', $_SERVER['HTTP_RANGE'])));

						if (empty($range[1]) === true)
						{
							$range[1] = $size - 1;
						}

						foreach ($range as $key => $value)
						{
							$range[$key] = max(0, min($value, $size - 1));
						}

						if (($range[0] > 0) || ($range[1] < ($size - 1)))
						{
							header(sprintf('%s %03u %s', 'HTTP/1.1', 206, 'Partial Content'), true, 206);
						}
					}

					header('Accept-Ranges: bytes');
					header('Content-Range: bytes ' . sprintf('%u-%u/%u', $range[0], $range[1], $size));
				}

				else
				{
					$range = array(0, $size - 1);
				}

				header('Pragma: public');
				header('Cache-Control: public, no-cache');
				header('Content-Type: '.$content_type);
				header('Content-Length: ' . sprintf('%u', $range[1] - $range[0] + 1));
				header('Content-Disposition: '. $content_disposition .'; filename="' . $filename . '"');
				header('Content-Transfer-Encoding: binary');

				if ($range[0] > 0)
				{
					fseek($file, $range[0]);
				}

				while ((feof($file) !== true) && (connection_status() === CONNECTION_NORMAL))
				{
					echo fread($file, round($speed * 1024)); flush(); sleep(1);
				}

				fclose($file);
			}
			// removing exit so unlink can be called on temp file after download
			//exit();
		}

		else
		{
			header(sprintf('%s %03u %s', 'HTTP/1.1', 404, 'Not Found'), true, 404);
		}

		return false;
	}
	if(!file_exists(DATA_FILEPATH))
		mkdir(DATA_FILEPATH, 0777, true);

	$tempname = tempnam(DATA_FILEPATH, "zipdownload");
	$zip = new ZipArchive();
	

	if ($zip->open($tempname, ZipArchive::CREATE)!==TRUE) {
		exit("cannot open <$tempname>\n");
	}

	foreach($files as $file)
	{
		$zip->addFile($file->StoredFilePath,$order->OrderId."_".$file->FileId."_".$file->FileName);
	}
	
	$details = $order->GetXml();
	$zip->addFromString($order->OrderId."_OrderDetails.xml", $details);
	
	$zip->close();
	Download($tempname, $order->OrderId . "_files.zip", "application/zip", "attachment");
	unlink($tempname);
}