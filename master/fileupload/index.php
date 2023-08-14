<?php 
    require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/InitDBAndSMTP.php");
    require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/SignInManager.php");
	require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/Orders.php"); 
	
	class OrderFileUploadInformation
    {
        public $OrderId = 0;
        public $FileId = 0;
        public $FileName = "";
        public $Length = 0;
        public $Written = 0;
        public $ContentType = "";
    }
	
	class OrderInsertResult
    {
        public $OrderId = 0;
        public $ViewOrderKey = "";
        public $UploadFileKey = "";

        public $FileInformation = array();
    }
	function uniqidReal($length = 13) {
		if(function_exists("random_bytes"))
		{
			$bytes = random_bytes(ceil($length / 2));
		}
		else if(function_exists("openssl_random_pseudo_bytes"))
		{
			$bytes = openssl_random_pseudo_bytes(ceil($length / 2));
		}
		else
		{
			throw new Exception("Failed to generate verification token.");
		}
		return substr(bin2hex($bytes), 0, $length);
	}
	if(isset($_POST) && count($_POST) >= 15)
	{
		$filesinfo = json_decode($_POST['FileInformation']);
		$result = new OrderInsertResult();
		$order = new Order();
		
		$order->ContactName = $_POST["Name"];
		$order->PhoneNumber = $_POST["PhoneNumber"];
		$order->EmailAddress = $_POST["EmailAddress"];
		$order->CompanyName = $_POST["CompanyName"];
		$order->Address1 = $_POST["Address1"];
		$order->Address2 = $_POST["Address2"];
		$order->City = $_POST["City"];
		$order->State = $_POST["State"];
		$order->ZipCode = $_POST["ZipCode"];
		$order->DueDate = $_POST["DateDue"];
		$order->DueTime = $_POST["LatestTimeDue"];
		$order->ProjectNumber = $_POST["ProjectNumber"];
		$order->PurchaseOrderNumber = $_POST["PONumber"];
		$order->ProjectName = $_POST["ProjectName"];
		$order->Notes = $_POST["Notes"];
		if(count($filesinfo) > 0)
			$order->Status = "Pending File Upload";
		else
			$order->Status = "Awaiting retrieval";
		$order->ViewOrderKey = uniqidReal();
		$order->UploadFileKey = uniqidReal();
		
		if($order->DueDate == "")
		{
			$order->DueDate = "1970-01-01";
		}
		
		$order->OrderId = $OrdersContext->SaveOrder($order);
		
		$result->OrderId = $order->OrderId;
		$result->ViewOrderKey = $order->ViewOrderKey;
        $result->UploadFileKey = $order->UploadFileKey;
		
		foreach($filesinfo as $fileinfo)
		{
			$path = "";
			
			$OrderFileInformation = new OrderFileUploadInformation();
			$OrderFileInformation->OrderId = $order->OrderId;
			
			$OrderFileInformation->FileId = $OrdersContext->SaveOrderFileInformationBeforeUpload($order, 0, $fileinfo->FileName, $fileinfo->FileName, $fileinfo->Length, $fileinfo->ContentType);
			$path = DATA_FILEPATH . "/" .  $OrderFileInformation->FileId;
			// Update path which required file id to build
			$OrdersContext->SaveOrderFileInformationBeforeUpload($order, $OrderFileInformation->FileId, $fileinfo->FileName, $path, $fileinfo->Length, $fileinfo->ContentType, 0);
			
			$OrderFileInformation->FileName = $fileinfo->FileName;
			$OrderFileInformation->Length = $fileinfo->Length;
			$OrderFileInformation->Written = 0;
			$OrderFileInformation->ContentType = $fileinfo->ContentType;
			
			$result->FileInformation[] = $OrderFileInformation;
		}
		
        //public $FileInformation = array();
		
		//if($order->OrderId > 0)
		{
			setcookie("name", $order->ContactName);
			setcookie("phonenumber", $order->PhoneNumber);
			setcookie("emailaddress", $order->EmailAddress);
			setcookie("companyname", $order->CompanyName);
			setcookie("address1", $order->Address1);
			setcookie("address2", $order->Address2);
			setcookie("city", $order->City);
			setcookie("state", $order->State);
			setcookie("zipcode", $order->ZipCode);
		}
				
		die(json_encode($result));
	}
?>
<?php require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/header.php"); ?>
<style>
    /* Font family for the entire body */
    body {
        font-family: Arial, sans-serif;
    }

    /* Styling for the form container */
    .formcontainer {
        width: 500px;
        margin: 0 auto;
    }

    /* Styling for the upload form */
    .uploadform {
        margin-top: 50px;
    }

    /* Styling for the form rows */
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 5px;
    }

    /* Styling for text input, select, and textarea elements */
    input[type=text], select, textarea {
        width: 100%;
        padding: 2px;
        border: 1px solid #ccc;
        box-sizing: border-box;
        margin-top: 2px;
        margin-bottom: 12px;
        resize: vertical;
    }

    /* Styling for the submit button */
    input[type=submit] {
        background-color: #4CAF50;
        color: white;
        padding: 12px 20px;
        border: none;
        cursor: pointer;
    }

        input[type=submit]:hover {
            background-color: #45a049;
        }

    /* Styling for h2 headings */
    h2 {
        color: #2c3e50;
    }

    /* Styling for the file upload area */
    .upload-area {
        border: 2px dashed #ccc;
        padding: 20px;
        text-align: center;
        margin-bottom: 16px;
        cursor: pointer;
        overflow: scroll;
        height: 150px;
    }

    /* Styling for buttons */
    button {
        background-color: #4CAF50;
        color: white;
        padding: 10px 20px;
        border: none;
        cursor: pointer;
        margin-bottom: 16px;
    }

    /* Styling for invalid feedback messages */
    .invalid-feedback {
        color: red;
    }

    /* Styling for input elements with invalid data */
    .is-invalid {
        border-color: red;
    }

    /* Styling for input elements with valid data */
    .is-valid {
        border-color: green;
    }

    /* Styling for the file list container */
    #file-list-container {
        width: 500px;
        height: 200px;
        overflow: scroll;
    }

    /* Styling for selected files in the file list */
    .selectedfile {
        background-color: #cce5ff;
        cursor: pointer;
    }

    /* Default styling for unordered list */
    ul {
    }

    /* Default styling for list items */
    li {
        text-align: left;
    }
</style>

<style>
    /* Styling for the modal (popup) */
    .modal {
        display: none; /* Hidden by default */
        position: fixed; /* Positioned relative to the viewport */
        z-index: 1; /* Ensures the modal appears on top of other content */
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto; /* Allow scrolling within the modal if content overflows */
        background-color: rgba(0,0,0,0.4); /* Semi-transparent black background, creating a dimming effect */
    }

    /* Styling for the content within the modal */
    .modal-content {
        background-color: #fefefe; /* Background color of the modal content area */
        margin: 15% auto; /* Centers the modal vertically with a 15% margin from the top */
        padding: 20px;
        border: 1px solid #888; /* Border around the modal content area */
        width: 80%; /* Sets the width of the modal content area to 80% of the viewport */
    }
</style>

<div class="text-center">
    <div class="formcontainer">
        <form method="post" enctype="multipart/form-data" onsubmit="submitForm(event)" id="OrderForm" class="uploadform">
            <input name="__RequestVerificationToken" type="hidden" value="">
            <div class="form-row">
                <div>
                    <h2>Contact Info</h2>
                    <input type="text" id="Name" name="Name" placeholder="Your name.." required="required" value="<?php print (isset($_COOKIE["name"])? $_COOKIE["name"] : ""); ?>">
                    <input type="text" id="PhoneNumber" name="PhoneNumber" placeholder="Your phone number.." required="required" value="<?php print (isset($_COOKIE["phonenumber"])? $_COOKIE["phonenumber"] : ""); ?>">
                    <input type="text" id="EmailAddress" name="EmailAddress" placeholder="Your email.." required="required" value="<?php print (isset($_COOKIE["emailaddress"])? $_COOKIE["emailaddress"] : ""); ?>">
                </div>
                <div>
                    <h2>Company Info</h2>
                    <input type="text" id="CompanyName" name="CompanyName" placeholder="Company name.." required="required" value="<?php print (isset($_COOKIE["companyname"])? $_COOKIE["companyname"] : ""); ?>">
                    <input type="text" id="Address1" name="Address1" placeholder="Address 1.." required="required" value="<?php print (isset($_COOKIE["address1"])? $_COOKIE["address1"] : ""); ?>">
                    <input type="text" id="Address2" name="Address2" placeholder="Address 2.." value="<?php print (isset($_COOKIE["address2"])? $_COOKIE["address2"] : ""); ?>">
                    <input type="text" id="City" name="City" placeholder="City.." required="required" value="<?php print (isset($_COOKIE["city"])? $_COOKIE["city"] : ""); ?>">
                    <input type="text" id="State" name="State" placeholder="State.." required="required" value="<?php print (isset($_COOKIE["state"])? $_COOKIE["state"] : ""); ?>">
                    <input type="text" id="ZipCode" name="ZipCode" placeholder="Zip.." required="required" value="<?php print (isset($_COOKIE["zipcode"])? $_COOKIE["zipcode"] : ""); ?>">
                </div>
            </div>
            <div class="form-row">
                <div>
                    <h2>Time Constraints</h2>
                    <input type="date" id="DateDue" name="DateDue">
                    <input type="time" id="LatestTimeDue" name="LatestTimeDue">
                </div>
                <div>
                    <h2>Accounting Details</h2>
                    <input type="text" id="ProjectNumber" name="ProjectNumber" placeholder="Project #..">
                    <input type="text" id="PONumber" name="PONumber" placeholder="PO #..">
                    <input type="text" id="ProjectName" name="ProjectName" placeholder="Project name..">
                </div>
            </div>
            <div>
                <h2>Production Specifics</h2>
                <textarea id="Notes" name="Notes" placeholder="Special Instructions.."></textarea>
            </div>

            <div>
                <h2>File Upload</h2>
                <div class="upload-area" id="drop-area">
                    Drag and Drop Files Here
                    <ul id="file-list"></ul>
                </div>
                <input type="file" id="FileSelector" name="FileSelector[]" multiple>
                <button type="button" id="remove-button">Remove Selected Items</button>
                <div></div>
            </div>
            <input type="submit" value="Submit">
        </form>
    </div>
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <p id="statusMessage"></p>
            <p id="fileUploadStatus"></p>
            <p id="totalStatus"></p>
        </div>
    </div>
</div>


<script>
    // Code to be executed when the document is ready
    $(document).ready(function () {
        // Configure form validation using the jQuery Validation plugin
        $('#OrderForm').validate({
            // Rules for each form field
            rules: {
                Name: {
                    required: true, // Name field is required
                    minlength: 2 // Name must be at least 2 characters long
                },
                PhoneNumber: {
                    required: true, // Phone number field is required
                    minlength: 10, // Phone number must be at least 10 digits long
                    number: true // Phone number must be a numeric value
                },
                EmailAddress: {
                    required: true, // Email address field is required
                    email: true // Email address must be in a valid email format
                },
                CompanyName: {
                    required: true, // Company name field is required
                },
                Address1: {
                    required: true, // Address1 field is required
                },
                City: {
                    required: true, // City field is required
                },
                State: {
                    required: true, // State field is required
                },
                ZipCode: {
                    required: true, // Zip code field is required
                    minlength: 5, // Zip code must be at least 5 digits long
                    number: true // Zip code must be a numeric value
                },
                DueDate: {
                    required: false, // Due date field is optional (not required)
                    date: true // Due date must be in a valid date format
                },
                LatestDueTime: {
                    required: false, // Latest due time field is optional (not required)
                },
                ProjectNumber: {
                    required: false, // Project number field is optional (not required)
                },
                PONumber: {
                    required: false, // PO number field is optional (not required)
                },
                ProjectName: {
                    required: false, // Project name field is optional (not required)
                },
                Notes: {
                    required: false, // Notes field is optional (not required)
                }
            },
            // Custom error messages for form fields (messages are empty in this example)
            messages: {},
            // Element to wrap the error messages (div in this case)
            errorElement: 'div',
            // Function to place the error messages after the invalid element
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                error.insertAfter(element);
            },
            // Function to highlight the input element when it's invalid
            highlight: function (element, errorClass, validClass) {
                $(element).addClass('is-invalid').removeClass('is-valid');
            },
            // Function to unhighlight the input element when it's valid
            unhighlight: function (element, errorClass, validClass) {
                $(element).addClass('is-valid').removeClass('is-invalid');
            }
        });
    });
</script>
<script>
    // Selecting the relevant DOM elements using jQuery
    const $fileSelector = $('#FileSelector');
    const $fileList = $('#file-list');
    const $removeButton = $('#remove-button');

    // Event handler for the file selector (when files are selected)
    $fileSelector.on('change', function (e) {
        var files = $fileSelector.get(0).files;

        // Loop through the selected files
        for (var i = 0; i < files.length; i++) {
            var item = files[i];
            // Create a new list item for each file and store the file information as attributes
            const $li = $('<li>')
                .text(item.name)
                .attr('data-path', item.name)
                .attr('data-attachment', item);
            // Append the list item to the file list element
            $fileList.append($li);
            // Store the file object as the 'attachment' property of the list item for later use
            $li.get(0).attachment = item;
        }
        // Clear the file selector value to allow selecting new files
        $fileSelector.val('');
    });

    // Event handler for clicking on a list item (to select/deselect the file)
    $fileList.on('click', 'li', function () {
        $(this).toggleClass('selectedfile'); // Toggle the 'selectedfile' class to highlight/unhighlight the selected file
    });

    // Event handler for the remove button (to remove selected files)
    $removeButton.on('click', function (e) {
        // Loop through each list item with the 'selectedfile' class
        $('.selectedfile').each(function () {
            const selectedfilePath = $(this).data('path');
            // Find the next unordered list ('ul') element after the selected list item
            const ul = $(this).next('ul');
            // If such an 'ul' element exists, remove it (presumably, it's associated with this file)
            if (ul.length == 1) {
                ul.remove();
            }
            // Remove the selected list item from the DOM
            $('.selectedfile').remove();
        });
    });
</script>
<script>
    // Define global variables to store file information and attachments
    var FileInformation = [];
    var attachments = [];
    var fileCount = 0;
    var processedFileCount = 0;

    // Function to handle the form submission event
    function submitForm(event) {
        event.preventDefault(); // Prevent the default form submission
		//if(!$('#OrderForm').valid())
		//	return;
        EnableDisableForm(false);
        UpdateStatus("Initializing...", "0%", "0%");
        ShowHideStatus(true);

        // Get the original form
        var originalForm = document.getElementById("OrderForm");

        // Create a new form copy of the original
        var newForm = new FormData(originalForm);

        // Append the first element of the form (assuming it's the primary data) to the new form
        newForm.append(originalForm.elements[0].name, originalForm.elements[0].value);

        // Constructor function to create file information objects
        function CreateFileInformation(filename, length, type) {
            this.FileName = filename;
            this.Length = length;
            this.ContentType = type;
        }

        // Clear the file information and attachments arrays and reset the file counters
        FileInformation = [];
        attachments = [];
        fileCount = 0;
        processedFileCount = 0;

        // Function to handle a processed file
        function handleProcessedFile(file) {
            processedFileCount++;
            // You may want to do something with the processed file here
        }

        // Asynchronous function to handle a file
        async function handleFile(file, index) {
            await file.file(function (item) {
                // Create a new file information object and add it to the FileInformation array
                var onefile = new CreateFileInformation(item.name, item.size, item.type);
                FileInformation[index] = onefile;
                // Add the file item to the attachments array
                attachments[index] = item;
            });
            handleProcessedFile(file);
        }

        // Loop through each 'li' element and handle its associated file
        $('li').each(function () {
            var file = $(this).data('attachment');

            if (file === undefined) return;

            // Get the actual file item from the 'attachment' property
            file = $(this).get(0).attachment;

            if (file.isFile) {
                // If the file is a File object, increment the fileCount and handle the file
                fileCount++;
                handleFile(file, fileCount - 1);
            } else if (file.isFile === undefined) {
                // If the file is not a File object, increment the fileCount, create a file information object, and add it to the arrays
                fileCount++;
                var onefile = new CreateFileInformation(file.name, file.size, file.type);
                FileInformation.push(onefile);
                attachments.push(file);
				handleProcessedFile(file);
            }
        });

        // Function to check if all files have been processed, and submit the form if ready
        function CheckFilesHandled() {
			if (processedFileCount >= fileCount) {
                // Convert the FileInformation array to a JSON string and append it to the new form
                var jsonString = JSON.stringify(FileInformation);
                newForm.append("FileInformation", jsonString);
                UpdateStatus("Submitting order...", "0%", "0%");
                // Create a new XMLHttpRequest to submit the form data
                var formSubmissionRequest = new XMLHttpRequest();

                // Define the event handler for the XMLHttpRequest's state change
                formSubmissionRequest.onreadystatechange = function () {
                    if (formSubmissionRequest.readyState == 4) {
                        if (formSubmissionRequest.status == 200) {
                            // If the form submission was successful, handle the awaiting files
                            HandleAwaitingFiles(formSubmissionRequest, attachments);
                        } else if (formSubmissionRequest.status == 400) {
                            // If the form submission resulted in a Bad Request (400), handle the submission failure
                            HandleSubmissionFailure(formSubmissionRequest);
                        } else {
                            // If the form submission resulted in an unknown error, handle the submission failure
                            HandleSubmissionFailure(formSubmissionRequest);
                        }
                    }
                };
                // Open the XMLHttpRequest with the original form's method and action
                formSubmissionRequest.open(originalForm.method, originalForm.action, true);
                // Send the new form data as the payload of the XMLHttpRequest
                formSubmissionRequest.send(newForm);
            } else {
                // If not all files have been processed, wait for 500 milliseconds and check again
                setTimeout(CheckFilesHandled, 500);
            }
        }

        // Start the process to check if all files have been processed
        setTimeout(CheckFilesHandled, 500);
    }


    // Define the FileUpload function which handles uploading files in chunks
    function FileUpload(OrderInsertResults) {
        // Store the OrderInsertResults object in the FileUpload instance
        this.OrderInsertResults = OrderInsertResults;
        // Initialize variables to keep track of file and write indices
        this.FileIndex = 0;
        this.WriteIndex = 0;
        // Set the chunk size to 10,240,000 bytes (10 MB)
        this.ChunkSize = 10240000;
        // Initialize variables to track the total bytes written and total file length
        this.TotalWritten = 0;
        this.TotalLength = 0;

        // Define the SendFiles function for handling the file upload process
        this.SendFiles = function (Upload) {
            // Check if this is the start of the file upload process
            if (Upload.FileIndex == 0 && Upload.WriteIndex == 0) {
                // Display a status message indicating the start of the file upload
                UpdateStatus("Starting file upload...", "0%", "0%");
            }
			if (Upload.FileIndex >= Upload.OrderInsertResults.FileInformation.length) {
				// Update status to show 100% progress for all files
				UpdateStatus("No files to upload.", "100%", "100%");
				// Redirect to the order successful page after a delay
				setTimeout(() => {
					window.location.href = "/fileupload/OrderSuccessful.php?id=" + Upload.OrderInsertResults.OrderId + "&viewOrderKey=" + Upload.OrderInsertResults.ViewOrderKey;
				}, 1000);
			}
            // Create a new FormData object to store data for the XMLHttpRequest
            var formData = new FormData();
            // Append relevant data to the form data
            formData.append('OrderId', Upload.OrderInsertResults.OrderId);
            formData.append('UploadFileKey', Upload.OrderInsertResults.UploadFileKey);
            formData.append('FileId', Upload.OrderInsertResults.FileInformation[Upload.FileIndex].FileId);

            // Set the action and method for the form data
            formData.action = "/fileupload/UploadFile.php";
            formData.method = "POST";

            // Get the file data from the OrderInsertResults object
            var file = OrderInsertResults.FileInformation[Upload.FileIndex].Blob;

            // Append a chunk of the file to the form data
            formData.append("file[]", file.slice(Upload.WriteIndex, Upload.WriteIndex + Upload.ChunkSize), file.name);

            // Create a new XMLHttpRequest object for uploading the chunk
            var ChunkUploadRequest = new XMLHttpRequest();

            // Open the XMLHttpRequest with the specified method and action
            ChunkUploadRequest.open(formData.method, formData.action, true);
            // Send the form data as the payload of the XMLHttpRequest
            ChunkUploadRequest.send(formData);

            // Define an event handler for the XMLHttpRequest's state change
            ChunkUploadRequest.onreadystatechange = function () {
                // Check if the request is complete
                if (ChunkUploadRequest.readyState == 4) {
                    // Check if the request was successful (status 200)
                    if (ChunkUploadRequest.status == 200) {
                        // Record the previous write index
                        var wasWriteIndex = Upload.WriteIndex;
                        // Update the write index to point to the next chunk
                        Upload.WriteIndex = Upload.WriteIndex + Upload.ChunkSize;
						//alert(ChunkUploadRequest.responseText);
                        // Check if the entire file has been uploaded
                        if (Upload.WriteIndex > file.size) {
                            // Move to the next file
                            Upload.FileIndex++;
                            // Update status to show progress for the next file
                            UpdateStatus("File " + (Upload.FileIndex + 1) + "/" + Upload.OrderInsertResults.FileInformation.length, "0%", Math.floor(Upload.TotalWritten / Upload.TotalLength * 100) + "%");
                            // Reset the write index to 0 for the next file
                            Upload.WriteIndex = 0;
                        }
                        else {
                            // Update the total bytes written and display progress for the current file
                            Upload.TotalWritten += Math.min(Upload.ChunkSize, OrderInsertResults.FileInformation[Upload.FileIndex].Blob.size - wasWriteIndex);
                            UpdateStatus("File " + (Upload.FileIndex + 1) + "/" + Upload.OrderInsertResults.FileInformation.length, Math.floor(Upload.WriteIndex / file.size * 100) + "%", Math.floor(Upload.TotalWritten / Upload.TotalLength * 100) + "%");
                        }

                        // Check if all files have been uploaded
                        if (Upload.FileIndex >= Upload.OrderInsertResults.FileInformation.length) {
                            // Update status to show 100% progress for all files
                            UpdateStatus("File " + (Upload.FileIndex) + "/" + Upload.OrderInsertResults.FileInformation.length, "100%", "100%");
                            // Redirect to the order successful page after a delay
                            setTimeout(() => {
                                window.location.href = "/fileupload/OrderSuccessful.php?id=" + Upload.OrderInsertResults.OrderId + "&viewOrderKey=" + Upload.OrderInsertResults.ViewOrderKey;
                            }, 1000);
                        }
                        else {
                            // Upload the next file recursively
                            Upload.SendFiles(Upload);
                        }
                    }
                    // Handle HTTP 400 Bad Request
                    else if (ChunkUploadRequest.status == 400) {
                        UpdateStatus("400 on chunk upload request", "0%", "0%");
                    }
                    // Handle other error responses
                    else {
                        UpdateStatus("Error: " + ChunkUploadRequest.responseText, "0%", "0%");
                    }
                }
            };
        }
    }

    // Global variable to store the FileUpload instance
    var upload;

    // Function to handle successful file uploads and initialize the FileUpload instance
    function HandleAwaitingFiles(ajax, attachments) {
        var response = ajax.responseText;
		var jsonObject = JSON.parse(response);
		var totalLength = 0;

		if(jsonObject.FileInformation != undefined)
		{
		// Assign the file Blob objects to the FileInformation objects in the response
			for (var i = 0; i < jsonObject.FileInformation.length; i++) {
				jsonObject.FileInformation[i].Blob = attachments[i];
				totalLength += attachments[i].size;
			}
		}
        // Create a new FileUpload instance and set the total length
        upload = new FileUpload(jsonObject);
        upload.TotalLength = totalLength;

        // Start sending files using the SendFiles function of the FileUpload instance
		
        upload.SendFiles(upload);
    }

    // Function to handle submission failure
    function HandleSubmissionFailure(ajax) {
        UpdateStatus("Order not processed... " + ajax.responseText, "0%", "0%");
    }

    // Function to handle upload failure
    function HandleUploadFailure(ajax) {
        UpdateStatus("Upload failure... " + ajax.responseText, "0%", "0%");
    }

    // Function to enable/disable form elements
    function EnableDisableForm(enabled) {
        if (enabled) {
            $(this).find(':input').prop('disabled', false);
        } else {
            $(this).find(':input').prop('disabled', true);
        }
    }

    // Function to show/hide the status modal
    function ShowHideStatus(show) {
        if (show) {
            $('#statusModal').css('display', 'block');
        } else {
            $('#statusModal').css('display', 'none');
        }
    }

    // Function to update the status message and progress indicators
    function UpdateStatus(message, percentIndividual, percentTotal) {
        const $statusParagraph = $('#statusMessage');
        const $percentIndividual = $('#fileUploadStatus');
        const $percentTotal = $('#totalStatus');

        // Update the status message and progress indicators with the provided values
        $statusParagraph.text(message);
        $percentIndividual.text("Individual File: " + percentIndividual);
        $percentTotal.text("Total Upload: " + percentTotal);
    }
</script>
<script>
    // Selecting the drop area element using jQuery
    const $dropArea = $('#drop-area');

    // Event handler for dragging over the drop area
    $dropArea.on('dragover', function (e) {
        e.preventDefault();
        e.stopPropagation();
        e.originalEvent.dataTransfer.dropEffect = 'copy'; // Set the drop effect to 'copy'
    });

    // Event handler for dropping files into the drop area
    $dropArea.on('drop', async function (e) {
        e.preventDefault();
        e.stopPropagation();

        // Clear the file list if needed (commented out in the original code)
        // $fileList.empty();

        const items = e.originalEvent.dataTransfer.items;

        // Loop through the items dropped into the drop area
        for (let i = 0; i < items.length; i++) {
            const item = items[i];
            // Call the scanFiles function to handle the dropped item (file or directory)
            scanFiles(item.webkitGetAsEntry(), $fileList, "");
        }
    });

    // Function to scan files and directories
    function scanFiles(item, container, parentPath) {
        const filePath = parentPath + '/' + item.name;
        const $li = $('<li>')
            .text(item.name)
            .attr('data-path', filePath)
            .attr('data-attachment', item);
        $li.get(0).attachment = item;
        container.append($li);

        var directoryContainer = false;
        // Check if the item is a directory
        if (item.isDirectory) {
            // Create a directory reader to read the contents of the directory
            let directoryReader = item.createReader();
            // Create a new container (ul) element for the directory
            directoryContainer = $('<ul>')
                .attr('data-path', filePath);
            // Append the directory container to the current container (ul)
            container.append(directoryContainer);

            // Read the entries (files and subdirectories) of the directory
            directoryReader.readEntries((entries) => {
                // Loop through the entries and recursively call scanFiles for each entry
                entries.forEach((entry) => {
                    scanFiles(entry, directoryContainer, parentPath + "/" + entry.name);
                });
            });
        }

        // If the item is a directory, store the directory container in the data-ul attribute of the list item
        if (directoryContainer != false)
            $li.attr('data-ul', directoryContainer);
    }
</script>
<?php 
    require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/footer.php");
