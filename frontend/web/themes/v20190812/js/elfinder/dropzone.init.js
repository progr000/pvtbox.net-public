var old_ev_targ = 'null';
var target_folder_id = -1;
var target_folder_hash = "";
var upl_pos_x = 100;
var upl_pos_y = 100;

/**
 *
 */
function minimizeUpload()
{
    $('#preview_uploads').hide();
    var elf_params = getElfinderParams();
    var minimize_top  = elf_params.pos_elf.top + elf_params.h_elf - elf_params.h_upl - 20;
    var minimize_left = elf_params.pos_elf.left + elf_params.w_elf - elf_params.w_upl - 12;
    $('#upload-dialog').css({top: minimize_top + 'px', left: minimize_left + 'px', height: 'auto'})
}

/**
 *
 */
function maximizeUpload()
{
    $('#preview_uploads').show();
    $('#upload-dialog').css({top: upl_pos_x + 'px', left: upl_pos_y + 'px', height: 'auto'})
}

/**
 *
 * @returns {*}
 */
function initDropzone()
{
    var elf_params = getElfinderParams();
    upl_pos_x = elf_params.pos_elf.top + 12;
    upl_pos_y = elf_params.w_elf - elf_params.w_upl + elf_params.pos_elf.left - 12;
    $('#upload-dialog').css({top: upl_pos_x + 'px', left: upl_pos_y + 'px'}).draggable({
        drag: function() {
            if ($('#preview_uploads').is(":visible")) {
                var pos_upl = $(this).offset();
                upl_pos_x = pos_upl.top;
                upl_pos_y = pos_upl.left;
                //console_log('new Top: ' + upl_pos_x + ' new Left: ' + upl_pos_y);
                $(this).css({ height: 'auto'});
            }
        }
    });
    $('#download-dialog-rtc').css({top: upl_pos_x + 'px', left: upl_pos_y + 'px'}).draggable({
        drag: function() {
            if ($('#rows_downloads-rtc').is(":visible")) {
                var pos_upl = $(this).offset();
                upl_pos_x = pos_upl.top;
                upl_pos_y = pos_upl.left;
                //console_log('new Top: ' + upl_pos_x + ' new Left: ' + upl_pos_y);
                $(this).css({ height: 'auto'});
            }
        }
    });

    $(document).on('click', '.ui-dialog-titlebar-min', function() {
        if ($('#preview_uploads').is(":visible")) {
            minimizeUpload();
        } else {
            maximizeUpload();
        }
    });

    $(document).on('click', '.ui-dialog-titlebar-close2', function() {
        //console_log(typeof myDropzone);
        if (typeof myDropzone != 'undefined') {
            /*
            if (confirm(myDropzone.options.dictCancelUploadConfirmation)) {
                var file, _i, _len, _ref, _results;
                _ref = myDropzone.files;
                _results = [];
                for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                    file = _ref[_i];
                    myDropzone.options.dictRemoveFileConfirmation = false;
                    myDropzone.removeFile(file);
                    //_results.push(myDropzone.cancelUpload(file));
                }
                $('#upload-dialog-tpl').hide();
            }
            */
            var file, _i, _len, _ref, _results;
            _ref = myDropzone.files;
            _results = [];
            prettyConfirm(function () {

                for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                    file = _ref[_i];
                    myDropzone.options.dictRemoveFileConfirmation = false;
                    myDropzone.removeFile(file);
                    //_results.push(myDropzone.cancelUpload(file));
                }
                $('#upload-dialog-tpl').hide();

            }, null, myDropzone.options.dictCancelUploadConfirmation, '', '');

        } else {
            $('#upload-dialog-tpl').hide();
        }
    });


    $(window).on('beforeunload' ,function() {
        if (typeof myDropzone != 'undefined') {
            var file, _i, _len, _ref, needConfirm;
            needConfirm = false;
            _ref = myDropzone.files;
            for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                file = _ref[_i];
                if (file.status === Dropzone.UPLOADING) {
                    needConfirm = true;
                }
            }
            if (needConfirm) {
                //snackbar('Upload in progress. Are you sure?', 'error', 5000, null, 'upload-canceled');
                return false;
                //self.trigger('unload');
            }
        }
    });

//    $(window).unload(function() {
        //if (typeof myDropzone != 'undefined') {
            /*
            var file, _i, _len, _ref, needConfirm;
            needConfirm = false;
            _ref = myDropzone.files;
            for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                file = _ref[_i];
                if (file.status === Dropzone.UPLOADING) {
                    needConfirm = true;
                }
            }
            */
            //if (needConfirm) {
            //if (true) {
//                return confirm_(myDropzone.options.dictCancelUploadConfirmation);
            //}
        //}
//    });

    // http://www.dropzonejs.com/#events
    // Get the template HTML and remove it from the doumenthe template HTML and remove it from the doument

    var previewNode = document.querySelector("#template_file_upload_tr");
    previewNode.id = "";
    var previewTemplate = previewNode.parentNode.innerHTML;
    previewNode.parentNode.removeChild(previewNode);

    //.elfinder-workzone
    //.elfinder-cwd-wrapper
    var myDropzone = new Dropzone(document.querySelector('#elfinder .elfinder-workzone'), { // Make the whole body a dropzone
        time_start: 0,
        url: _LANG_URL + "/user/add-files", // Set the url
        paramName: "UploadFilesForm[uploadedFile]",
        //method: "post",
        //enctype: "multipart/form-data",
        //autoProcessQueue: true,
        autoQueue: true,
        thumbnailWidth: 80,
        thumbnailHeight: 80,
        parallelUploads: 20,
        maxFilesize: 2048,
        previewTemplate: previewTemplate,
        //autoQueue: false, // Make sure the files aren't queued until manually added
        previewsContainer: "#preview_uploads", // Define the container to display the previews
        clickable: ".btn-exec-uploadFile", // Define the element that should be used as click trigger to select files.
        dictResponseError: "An error has occurred. Try again please."
    });

    //console_log(myDropzone);

    myDropzone.on("addedfile", function(file) {
        if ($('.btn-uploadFile').hasClass('notActive')) {
            elfinderInstance.error("Can't upload the file into deleted folder.");
            return false;
        }
        //console_log(this);
        if (this.options.time_start == 0) {
            var d = new Date();
            this.options.time_start = d.getTime();
            //console_log(this.options.time_start);
        }
        // Hookup the start button
        //$('#preview_uploads').show();
        maximizeUpload();
        $('#upload-dialog-tpl').show();

        //myDropzone.enqueueFile(file);
        //myDropzone.processQueue();
        //console_log(file);
        //file.previewElement.querySelector(".start").onclick = function() { myDropzone.enqueueFile(file); };
    });

    myDropzone.on("uploadprogress", function(file, progress, bytesSent) {
        var node, _i, _j, _len, _len1, _ref, _ref1, _results;
        if (file.previewElement) {
            _ref = file.previewElement.querySelectorAll("[data-dz-uploadprogress]");
            _results = [];
            for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                node = _ref[_i];
                if (node.nodeName === 'PROGRESS') {
                    _results.push(node.value = progress);
                } else {
                    node.style.width = "" + progress + "%";
                    //node.innerHTML = progress + '%';
                    _results.push(node);
                }
            }

            _ref1 = file.previewElement.querySelectorAll("[data-dz-size]");
            if (_ref1) {
                for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
                    node = _ref1[_j];
                    //node.innerHTML = this.filesize(bytesSent) + '/' + (this.filesize(file.size));
                    //node.innerHTML = file_size_format(file.size, 0);
                    node.innerHTML = file_size_format(bytesSent, 1) + '/' + file_size_format(file.size, 1);
                }
            }

            return _results;
        }
    });

    // Update the total progress bar
    myDropzone.on("totaluploadprogress", function(progress, total, bytesSent) {
        var d = new Date();
        var t = d.getTime();
        var progres_sec = parseInt((t - this.options.time_start) /1000);
        var speed = (file_size_format(bytesSent/progres_sec, 0) + 'ps');
        //console_log(total + ' / ' + bytesSent);
        document.querySelector("#upload-dialog .file-info-total").innerHTML = file_size_format(bytesSent, 1) + '/' + file_size_format(total, 1) + ', ' + speed;
        document.querySelector("#total-progress .progress-bar").style.width = progress + "%";
    });

    myDropzone.on("sending", function(file, xhr, formData) {
        //$('#file-upload-modal').modal({"show": true});
        formData.append("target_folder_id", target_folder_id);
        formData.append("count_node_online", nodesOnline.length);
        // Show the total progress bar when upload starts
        document.querySelector("#total-progress").style.opacity = "1";
        // And disable the start button
        //file.previewElement.querySelector(".start").setAttribute("disabled", "disabled");
    });

    // Hide the total progress bar when nothing's uploading anymore
    myDropzone.on("queuecomplete", function(progress) {
        this.options.time_start = 0;
        var cwd = elfinderInstance.cwd();
        //$('#upload-dialog-tpl').hide();
        if ((typeof target_folder_hash != 'undefined') && (target_folder_hash != "") && (target_folder_hash != cwd.hash)) {
            console_log(target_folder_hash);
            elfinderInstance.exec('open', target_folder_hash);
        } else {
            elfinderInstance.exec('reload');
        }
        if (!$('#preview_uploads').find('.file-row').length) {
            $('#upload-dialog-tpl').hide();
            document.querySelector("#total-progress").style.opacity = "0";
            document.querySelector("#total-progress .progress-bar").style.width = "0%";
        }
    });

    myDropzone.on("removedfile", function(file) {
        if (!$('#preview_uploads').find('.file-row').length) {
            $('#upload-dialog-tpl').hide();
            document.querySelector("#total-progress").style.opacity = "0";
            document.querySelector("#total-progress .progress-bar").style.width = "0%";
        }
    });

    myDropzone.on("success", function(file, response) {
        elfinderInstance.exec('reload');
        //console_log(file);
        //console_log(response);
        if (response.status) {
            this.removeFile(file);
        } else {
            //snackbar(response.info, 'error', 3000, null, 'upload-failed');
            elfinderInstance.error(response.info);
        }
    });

    myDropzone.on("drop", function(event) {
        if (typeof event.target.attributes['data-target-id'] == 'object') {
            //console_log(this);
            console_log(event.target.attributes['data-target-id'].value);
            target_folder_id = event.target.attributes['data-target-id'].value;
            target_folder_hash = event.target.attributes['data-target-hash'].value;
        } else if (typeof event.target.attributes['navbar-data-target-id'] == 'object') {
            console_log(event.target.attributes['navbar-data-target-id'].value);
            target_folder_id = event.target.attributes['navbar-data-target-id'].value;
            target_folder_hash = event.target.attributes['navbar-data-target-hash'].value;
        } else {
            target_folder_id = -1;
            target_folder_hash = "";
            console_log('no data-target-id');
        }
    });

    myDropzone.on("dragover", function(event) {
        /* Тут обработка основной зоны дропа в елфайндере */
        //console_log(event.target.attributes['data-target-hash']);
        $('#elfinder').find('.elfinder-cwd-wrapper').first().find('tr').each(function() {
            $(this).removeClass('ui-state-hover');
        });
        $('#elfinder').find('.elfinder-cwd-wrapper').first().find('div.elfinder-cwd-file').each(function() {
            $(this).removeClass('ui-state-hover');
        });
        if (typeof event.target.attributes['data-target-hash'] == 'object') {
            //console_log('ID=' + $('#' + event.target.attributes['data-target-hash'].value).attr('id'));
            $('#' + event.target.attributes['data-target-hash'].value).addClass('ui-state-hover');
        }

        /* Тут обработка зоны дропа TREE в елфайндере */
        //console_log(elfinderInstance.request({cmd : 'tree', target : 'l1_ZjI'}));
        $('#elfinder').find('.elfinder-tree').first().find('.elfinder-navbar-dir').each(function() {
            $(this).removeClass('ui-state-hover');
        });
        if (typeof event.target.attributes['navbar-data-target-hash'] == 'object') {
            var fh = event.target.attributes['navbar-data-target-hash'].value;
            var $dirobj = $('#nav-' + fh);
            $dirobj.addClass('ui-state-hover');
            console_log($dirobj.attr('class'));
            if (/*!$dirobj.hasClass('elfinder-subtree-loaded') &&*/ (old_ev_targ != fh)) {
                old_ev_targ = fh;
                elfinderInstance.exec('open', fh).done(function () {
                    //event.stopPropagation();
                    old_ev_targ = fh;
                    $dirobj.parent().find('.elfinder-navbar-subtree').first().delay(500).slideDown();
                });
            } else {
                $dirobj.parent().find('.elfinder-navbar-subtree').first().delay(500).slideDown();
            }
        } else {
            old_ev_targ = "";
        }
    });
    /*
    myDropzone.on("complete", function(file) {
        myDropzone.removeFile(file);
    });
    */

    // Setup the buttons for all transfers
    // The "add files" button doesn't need to be setup because the config
    // `clickable` has already been specified.
    /*
    document.querySelector("#actions .start").onclick = function() {
        myDropzone.enqueueFiles(myDropzone.getFilesWithStatus(Dropzone.ADDED));
    };
    document.querySelector("#actions .cancel").onclick = function() {
        myDropzone.removeAllFiles(true);
    };
    */

    return myDropzone;
}


