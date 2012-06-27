var topWin = window.dialogArguments || opener || parent || top; 

function lg_fileDialogStart() {
	jQuery("#media-upload-error").empty();
  jQuery('#refresh-folder').hide();
}

// progress and success handlers for media multi uploads
function lg_fileQueued(fileObj) {
	// Get rid of unused form
	jQuery('.media-blank').remove();
	// Collapse a single item
	if ( jQuery('form.type-form #media-items').children().length == 1 && jQuery('.hidden', '#media-items').length > 0 ) {
		jQuery('.describe-toggle-on').show();
		jQuery('.describe-toggle-off').hide();
		jQuery('.slidetoggle').slideUp(200).siblings().removeClass('hidden');
	}
	// Create a progress bar containing the filename
	jQuery('#media-items').append('<div id="media-item-' + fileObj.id + '" class="media-item child-of-' + folder_id + '"><div class="progress"><div class="bar"></div></div><div class="filename original"><span class="percent"></span> ' + fileObj.name + '</div></div>');
	// Display the progress div
	jQuery('.progress', '#media-item-' + fileObj.id).show();

	// Disable submit and enable cancel
	jQuery('#insert-gallery').attr('disabled', 'disabled');
	jQuery('#cancel-upload').attr('disabled', '');
}

function lg_uploadStart(fileObj) {
	try {
		if ( typeof topWin.tb_remove != 'undefined' )
			topWin.jQuery('#TB_overlay').unbind('click', topWin.tb_remove); 
	} catch(e){}

	return true;
}

function lg_uploadProgress(fileObj, bytesDone, bytesTotal) {
	// Lengthen the progress bar
	var w = jQuery('#media-items').width() - 2, item = jQuery('#media-item-' + fileObj.id);
	jQuery('.bar', item).width( w * bytesDone / bytesTotal );
	jQuery('.percent', item).html( Math.ceil(bytesDone / bytesTotal * 100) + '%' );

	if ( bytesDone == bytesTotal )
		jQuery('.bar', item).html('<strong class="crunching">' + lg_swfuploadL10n.crunching + '</strong>');
}

function lg_prepareMediaItem(fileObj, serverData) {
	// Move the progress bar to 100%
	jQuery('.bar', item).remove();
	jQuery('.progress', item).hide();

	try {
		if ( typeof topWin.tb_remove != 'undefined' )
			topWin.jQuery('#TB_overlay').click(topWin.tb_remove);
	} catch(e){}

	// Old style: Append the HTML returned by the server -- thumbnail and form inputs
	if ( isNaN(serverData) || ! serverData ) {
		item.append(serverData);
	}
}


function lg_uploadSuccess(fileObj, serverData) {
	// if async-upload returned an error message, place it in the media item div and return
	if ( serverData.match('media-upload-error') ) {
		jQuery('#media-item-' + fileObj.id).html(serverData);
		return;
	}
	topWin.lg_refreshFolder();
  jQuery('.crunching').html(lg_swfuploadL10n.ready);
}

function lg_uploadComplete(fileObj) {
	// If no more uploads queued, enable the submit button
	if ( swfu.getStats().files_queued == 0 ) {
		jQuery('#cancel-upload').attr('disabled', 'disabled');
	}
}


// wp-specific error handlers

// generic message
function lg_QueueError(message) {
	jQuery('#media-upload-error').show().text(message);
}

// file-specific message
function lg_FileError(fileObj, message) {
	jQuery('#media-item-' + fileObj.id + ' .filename').after('<div class="file-error"><button type="button" id="dismiss-' + fileObj.id + '" class="button dismiss">'+lg_swfuploadL10n.dismiss+'</button>'+message+'</div>').siblings('.toggle').remove();
	jQuery('#dismiss-' + fileObj.id).click(function(){jQuery(this).parents('.media-item').slideUp(200, function(){jQuery(this).remove();})});
}

function lg_fileQueueError(fileObj, error_code, message)  {
	// Handle this error separately because we don't want to create a FileProgress element for it.
	if ( error_code == SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED ) {
		lg_QueueError(lg_swfuploadL10n.queue_limit_exceeded);
	}
	else if ( error_code == SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT ) {
		lg_fileQueued(fileObj);
		lg_FileError(fileObj, lg_swfuploadL10n.file_exceeds_size_limit);
	}
	else if ( error_code == SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE ) {
		lg_fileQueued(fileObj);
		lg_FileError(fileObj, lg_swfuploadL10n.zero_byte_file);
	}
	else if ( error_code == SWFUpload.QUEUE_ERROR.INVALID_FILETYPE ) {
		fileQueued(fileObj);
		lg_FileError(fileObj, lg_swfuploadL10n.invalid_filetype);
	}
	else {
		lg_QueueError(lg_swfuploadL10n.default_error);
	}
}

function lg_fileDialogComplete(num_files_queued) {
	try {
		if (num_files_queued > 0) {
			this.startUpload();
		}
	} catch (ex) {
		this.debug(ex);
	}
}

function lg_switchUploader(s) {
	var f = document.getElementById(swfu.customSettings.swfupload_element_id), h = document.getElementById(swfu.customSettings.degraded_element_id);
	if ( s ) {
		f.style.display = 'block';
		h.style.display = 'none';
	} else {
		f.style.display = 'none';
		h.style.display = 'block';
	}
}

function lg_swfuploadPreLoad() {
}

function lg_swfuploadLoadFailed() {
	lg_switchUploader(0);
	jQuery('.upload-html-bypass').hide();
}

function lg_uploadError(fileObj, errorCode, message) {

	switch (errorCode) {
		case SWFUpload.UPLOAD_ERROR.MISSING_UPLOAD_URL:
			lg_FileError(fileObj, lg_swfuploadL10n.missing_upload_url);
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
			lg_FileError(fileObj, lg_swfuploadL10n.upload_limit_exceeded);
			break;
		case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
			lg_QueueError(lg_swfuploadL10n.http_error);
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
			lg_QueueError(lg_swfuploadL10n.upload_failed);
			break;
		case SWFUpload.UPLOAD_ERROR.IO_ERROR:
			lg_QueueError(lg_swfuploadL10n.io_error);
			break;
		case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
			lg_QueueError(lg_swfuploadL10n.security_error);
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
		case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
			jQuery('#media-item-' + fileObj.id).remove();
			break;
		default:
			lg_FileError(fileObj, lg_swfuploadL10n.default_error);
	}
}

function lg_cancelUpload() {
	swfu.cancelQueue();
}