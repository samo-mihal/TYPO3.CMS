/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
define(["require","exports","jquery","moment","nprogress","TYPO3/CMS/Backend/Modal","./Notification","./Severity"],function(a,b,c,d,e,f,g,h){"use strict";Object.defineProperty(b,"__esModule",{value:!0});var i;!function(a){a.OVERRIDE="replace",a.RENAME="rename",a.SKIP="cancel",a.USE_EXISTING="useExisting"}(i||(i={}));var j=function(){function a(a){var b=this;this.askForOverride=[],this.percentagePerFile=1,this.dragFileIntoDocument=function(a){return a.stopPropagation(),a.preventDefault(),c(a.currentTarget).addClass("drop-in-progress"),b.showDropzone(),!1},this.dragAborted=function(a){return a.stopPropagation(),a.preventDefault(),c(a.currentTarget).removeClass("drop-in-progress"),!1},this.ignoreDrop=function(a){return a.stopPropagation(),a.preventDefault(),b.dragAborted(a),!1},this.handleDrop=function(a){b.ignoreDrop(a),b.processFiles(a.originalEvent.dataTransfer.files),b.$dropzone.removeClass("drop-status-ok")},this.fileInDropzone=function(){b.$dropzone.addClass("drop-status-ok")},this.fileOutOfDropzone=function(){b.$dropzone.removeClass("drop-status-ok")},this.$body=c("body"),this.$element=c(a);var d=void 0!==this.$element.data("dropzoneTrigger");this.$trigger=c(this.$element.data("dropzoneTrigger")),this.$dropzone=c("<div />").addClass("dropzone").hide(),this.irreObjectUid=this.$element.data("fileIrreObject");var e=this.$element.data("dropzoneTarget");return this.irreObjectUid&&0!==this.$element.nextAll(e).length?(this.dropZoneInsertBefore=!0,this.$dropzone.insertBefore(e)):(this.dropZoneInsertBefore=!1,this.$dropzone.insertAfter(e)),this.$dropzoneMask=c("<div />").addClass("dropzone-mask").appendTo(this.$dropzone),this.fileInput=document.createElement("input"),this.fileInput.setAttribute("type","file"),this.fileInput.setAttribute("multiple","multiple"),this.fileInput.setAttribute("name","files[]"),this.fileInput.classList.add("upload-file-picker"),this.$body.append(this.fileInput),this.$fileList=c(this.$element.data("progress-container")),this.fileListColumnCount=c("thead tr:first th",this.$fileList).length,this.filesExtensionsAllowed=this.$element.data("file-allowed"),this.fileDenyPattern=this.$element.data("file-deny-pattern")?new RegExp(this.$element.data("file-deny-pattern"),"i"):null,this.maxFileSize=parseInt(this.$element.data("max-file-size"),10),this.target=this.$element.data("target-folder"),this.browserCapabilities={fileReader:"undefined"!=typeof FileReader,DnD:"draggable"in document.createElement("span"),Progress:"upload"in new XMLHttpRequest},this.browserCapabilities.DnD?(this.$body.on("dragover",this.dragFileIntoDocument),this.$body.on("dragend",this.dragAborted),this.$body.on("drop",this.ignoreDrop),this.$dropzone.on("dragenter",this.fileInDropzone),this.$dropzoneMask.on("dragenter",this.fileInDropzone),this.$dropzoneMask.on("dragleave",this.fileOutOfDropzone),this.$dropzoneMask.on("drop",function(a){return b.handleDrop(a)}),this.$dropzone.prepend('<div class="dropzone-hint"><div class="dropzone-hint-media"><div class="dropzone-hint-icon"></div></div><div class="dropzone-hint-body"><h3 class="dropzone-hint-title">'+TYPO3.lang["file_upload.dropzonehint.title"]+'</h3><p class="dropzone-hint-message">'+TYPO3.lang["file_upload.dropzonehint.message"]+"</p></div></div>").click(function(){b.fileInput.click()}),c("<span />").addClass("dropzone-close").click(this.hideDropzone).appendTo(this.$dropzone),0===this.$fileList.length&&(this.$fileList=c("<table />").attr("id","typo3-filelist").addClass("table table-striped table-hover upload-queue").html("<tbody></tbody>").hide(),this.dropZoneInsertBefore?this.$fileList.insertAfter(this.$dropzone):this.$fileList.insertBefore(this.$dropzone),this.fileListColumnCount=7),this.fileInput.addEventListener("change",function(){b.processFiles(Array.apply(null,b.fileInput.files))}),void this.bindUploadButton(d===!0?this.$trigger:this.$element)):void console.warn("Browser has no Drag and drop capabilities; cannot initialize DragUploader")}return a.prototype.showDropzone=function(){this.$dropzone.show()},a.prototype.hideDropzone=function(a){a.stopPropagation(),a.preventDefault(),this.$dropzone.hide()},a.prototype.processFiles=function(a){var b=this;this.queueLength=a.length,this.$fileList.is(":visible")||this.$fileList.show(),e.start(),this.percentagePerFile=1/a.length;var d=[];c.each(a,function(a,f){d[parseInt(a,10)]=c.ajax({url:TYPO3.settings.ajaxUrls.file_exists,data:{fileName:f.name,fileTarget:b.target},cache:!1,success:function(a){var c="undefined"!=typeof a.uid;if(c)b.askForOverride.push({original:a,uploaded:f,action:b.irreObjectUid?i.USE_EXISTING:i.SKIP}),e.inc(b.percentagePerFile);else{new k(b,f,i.SKIP)}}})}),c.when.apply(c,d).done(function(){b.drawOverrideModal(),e.done()}),this.fileInput.value=""},a.prototype.bindUploadButton=function(a){var b=this;a.click(function(a){a.preventDefault(),b.fileInput.click(),b.showDropzone()})},a.prototype.decrementQueueLength=function(){this.queueLength>0&&(this.queueLength--,0===this.queueLength&&c.ajax({url:TYPO3.settings.ajaxUrls.flashmessages_render,cache:!1,success:function(a){c.each(a,function(a,b){g.showMessage(b.title,b.message,b.severity)})}}))},a.prototype.drawOverrideModal=function(){var a=this,b=Object.keys(this.askForOverride).length;if(0!==b){for(var e=c("<div/>").append(c("<p/>").text(TYPO3.lang["file_upload.existingfiles.description"]),c("<table/>",{class:"table"}).append(c("<thead/>").append(c("<tr />").append(c("<th/>"),c("<th/>").text(TYPO3.lang["file_upload.header.originalFile"]),c("<th/>").text(TYPO3.lang["file_upload.header.uploadedFile"]),c("<th/>").text(TYPO3.lang["file_upload.header.action"]))))),g=0;g<b;++g){var j=c("<tr />").append(c("<td />").append(""!==this.askForOverride[g].original.thumbUrl?c("<img />",{src:this.askForOverride[g].original.thumbUrl,height:40}):c(this.askForOverride[g].original.icon)),c("<td />").html(this.askForOverride[g].uploaded.name+" ("+l.fileSizeAsString(this.askForOverride[g].uploaded.size)+")<br>"+d(this.askForOverride[g].uploaded.lastModifiedDate,"x").format("YYYY-MM-DD HH:mm")),c("<td />").html(this.askForOverride[g].uploaded.name+" ("+l.fileSizeAsString(this.askForOverride[g].original.size)+")<br>"+d(this.askForOverride[g].original.mtime,"X").format("YYYY-MM-DD HH:mm")),c("<td />").append(c("<select />",{class:"form-control t3js-actions","data-override":g}).append(this.irreObjectUid?c("<option/>").val(i.USE_EXISTING).text(TYPO3.lang["file_upload.actions.use_existing"]):"",c("<option />").val(i.SKIP).text(TYPO3.lang["file_upload.actions.skip"]),c("<option />").val(i.RENAME).text(TYPO3.lang["file_upload.actions.rename"]),c("<option />").val(i.OVERRIDE).text(TYPO3.lang["file_upload.actions.override"]))));e.find("table").append("<tbody />").append(j)}var m=f.confirm(TYPO3.lang["file_upload.existingfiles.title"],e,h.warning,[{text:c(this).data("button-close-text")||TYPO3.lang["file_upload.button.cancel"]||"Cancel",active:!0,btnClass:"btn-default",name:"cancel"},{text:c(this).data("button-ok-text")||TYPO3.lang["file_upload.button.continue"]||"Continue with selected actions",btnClass:"btn-warning",name:"continue"}],["modal-inner-scroll"]);m.find(".modal-dialog").addClass("modal-lg"),m.find(".modal-footer").prepend(c("<span/>").addClass("form-inline").append(c("<label/>").text(TYPO3.lang["file_upload.actions.all.label"]),c("<select/>",{class:"form-control t3js-actions-all"}).append(c("<option/>").val("").text(TYPO3.lang["file_upload.actions.all.empty"]),this.irreObjectUid?c("<option/>").val(i.USE_EXISTING).text(TYPO3.lang["file_upload.actions.all.use_existing"]):"",c("<option/>").val(i.SKIP).text(TYPO3.lang["file_upload.actions.all.skip"]),c("<option/>").val(i.RENAME).text(TYPO3.lang["file_upload.actions.all.rename"]),c("<option/>").val(i.OVERRIDE).text(TYPO3.lang["file_upload.actions.all.override"]))));var n=this;m.on("change",".t3js-actions-all",function(){var a=c(this),b=a.val();""!==b?m.find(".t3js-actions").each(function(a,d){var e=c(d),f=parseInt(e.data("override"),10);e.val(b).prop("disabled","disabled"),n.askForOverride[f].action=e.val()}):m.find(".t3js-actions").removeProp("disabled")}).on("change",".t3js-actions",function(){var a=c(this),b=parseInt(a.data("override"),10);n.askForOverride[b].action=a.val()}).on("button.clicked",function(a){"cancel"===a.target.name?(n.askForOverride=[],f.dismiss()):"continue"===a.target.name&&(c.each(n.askForOverride,function(a,b){if(b.action===i.USE_EXISTING)l.addFileToIrre(n.irreObjectUid,b.original);else if(b.action!==i.SKIP){new k(n,b.uploaded,b.action)}}),n.askForOverride=[],f.dismiss())}).on("hidden.bs.modal",function(){a.askForOverride=[]})}},a}(),k=function(){function a(a,b,d){var e=this;if(this.dragUploader=a,this.file=b,this.override=d,this.$row=c("<tr />").addClass("upload-queue-item uploading"),this.$iconCol=c("<td />").addClass("col-icon").appendTo(this.$row),this.$fileName=c("<td />").text(b.name).appendTo(this.$row),this.$progress=c("<td />").attr("colspan",this.dragUploader.fileListColumnCount-2).appendTo(this.$row),this.$progressContainer=c("<div />").addClass("upload-queue-progress").appendTo(this.$progress),this.$progressBar=c("<div />").addClass("upload-queue-progress-bar").appendTo(this.$progressContainer),this.$progressPercentage=c("<span />").addClass("upload-queue-progress-percentage").appendTo(this.$progressContainer),this.$progressMessage=c("<span />").addClass("upload-queue-progress-message").appendTo(this.$progressContainer),0===c("tbody tr.upload-queue-item",this.dragUploader.$fileList).length?(this.$row.prependTo(c("tbody",this.dragUploader.$fileList)),this.$row.addClass("last")):this.$row.insertBefore(c("tbody tr.upload-queue-item:first",this.dragUploader.$fileList)),this.$iconCol.html('<span class="t3-icon t3-icon-mimetypes t3-icon-other-other">&nbsp;</span>'),this.dragUploader.maxFileSize>0&&this.file.size>this.dragUploader.maxFileSize)this.updateMessage(TYPO3.lang["file_upload.maxFileSizeExceeded"].replace(/\{0\}/g,this.file.name).replace(/\{1\}/g,l.fileSizeAsString(this.dragUploader.maxFileSize))),this.$row.addClass("error");else if(this.dragUploader.fileDenyPattern&&this.file.name.match(this.dragUploader.fileDenyPattern))this.updateMessage(TYPO3.lang["file_upload.fileNotAllowed"].replace(/\{0\}/g,this.file.name)),this.$row.addClass("error");else if(this.checkAllowedExtensions()){this.updateMessage("- "+l.fileSizeAsString(this.file.size));var f=new FormData;f.append("data[upload][1][target]",this.dragUploader.target),f.append("data[upload][1][data]","1"),f.append("overwriteExistingFiles",this.override),f.append("redirect",""),f.append("upload_1",this.file);var g=c.extend(!0,{},c.ajaxSettings,{url:TYPO3.settings.ajaxUrls.file_process,contentType:!1,processData:!1,data:f,cache:!1,type:"POST",success:function(a){return e.uploadSuccess(a)},error:function(a){return e.uploadError(a)}});g.xhr=function(){var a=c.ajaxSettings.xhr();return a.upload.addEventListener("progress",function(a){return e.updateProgress(a)}),a},this.upload=c.ajax(g)}else this.updateMessage(TYPO3.lang["file_upload.fileExtensionExpected"].replace(/\{0\}/g,this.dragUploader.filesExtensionsAllowed)),this.$row.addClass("error")}return a.prototype.updateMessage=function(a){this.$progressMessage.text(a)},a.prototype.removeProgress=function(){this.$progress&&this.$progress.remove()},a.prototype.uploadStart=function(){this.$progressPercentage.text("(0%)"),this.$progressBar.width("1%"),this.dragUploader.$trigger.trigger("uploadStart",[this])},a.prototype.uploadError=function(a){this.updateMessage(TYPO3.lang["file_upload.uploadFailed"].replace(/\{0\}/g,this.file.name));var b=c(a.responseText);b.is("t3err")?this.$progressPercentage.text(b.text()):this.$progressPercentage.text("("+a.statusText+")"),this.$row.addClass("error"),this.dragUploader.decrementQueueLength(),this.dragUploader.$trigger.trigger("uploadError",[this,a])},a.prototype.updateProgress=function(a){var b=Math.round(a.loaded/a.total*100)+"%";this.$progressBar.outerWidth(b),this.$progressPercentage.text(b),this.dragUploader.$trigger.trigger("updateProgress",[this,b,a])},a.prototype.uploadSuccess=function(a){var b=this;a.upload&&(this.dragUploader.decrementQueueLength(),this.$row.removeClass("uploading"),this.$fileName.text(a.upload[0].name),this.$progressPercentage.text(""),this.$progressMessage.text("100%"),this.$progressBar.outerWidth("100%"),a.upload[0].icon&&this.$iconCol.html('<a href="#" class="t3js-contextmenutrigger" data-uid="'+a.upload[0].id+'" data-table="sys_file">'+a.upload[0].icon+"&nbsp;</span></a>"),this.dragUploader.irreObjectUid?(l.addFileToIrre(this.dragUploader.irreObjectUid,a.upload[0]),setTimeout(function(){b.$row.remove(),0===c("tr",b.dragUploader.$fileList).length&&(b.dragUploader.$fileList.hide(),b.dragUploader.$trigger.trigger("uploadSuccess",[b,a]))},3e3)):setTimeout(function(){b.showFileInfo(a.upload[0]),b.dragUploader.$trigger.trigger("uploadSuccess",[b,a])},3e3))},a.prototype.showFileInfo=function(a){this.removeProgress();for(var b=7;b<this.dragUploader.fileListColumnCount;b++)c("<td />").text("").appendTo(this.$row);c("<td />").text(a.extension.toUpperCase()).appendTo(this.$row),c("<td />").text(a.date).appendTo(this.$row),c("<td />").text(l.fileSizeAsString(a.size)).appendTo(this.$row);var d="";a.permissions.read&&(d+='<strong class="text-danger">'+TYPO3.lang["permissions.read"]+"</strong>"),a.permissions.write&&(d+='<strong class="text-danger">'+TYPO3.lang["permissions.write"]+"</strong>"),c("<td />").html(d).appendTo(this.$row),c("<td />").text("-").appendTo(this.$row)},a.prototype.checkAllowedExtensions=function(){if(!this.dragUploader.filesExtensionsAllowed)return!0;var a=this.file.name.split(".").pop(),b=this.dragUploader.filesExtensionsAllowed.split(",");return c.inArray(a.toLowerCase(),b)!==-1},a}(),l=function(){function a(){}return a.fileSizeAsString=function(a){var b=a/1024,c="";return c=b>1024?(b/1024).toFixed(1)+" MB":b.toFixed(1)+" KB"},a.addFileToIrre=function(a,b){window.inline.delayedImportElement(a,"sys_file",b.uid,"file")},a.init=function(){var a=this,b=a.options;c.fn.extend({dragUploader:function(a){return this.each(function(b,d){var e=c(d),f=e.data("DragUploaderPlugin");f||e.data("DragUploaderPlugin",f=new j(d)),"string"==typeof a&&f[a]()})}}),c(function(){c(".t3js-drag-uploader").dragUploader(b)})},a}();b.initialize=function(){l.init(),"undefined"!=typeof TYPO3.settings&&"undefined"!=typeof TYPO3.settings.RequireJS&&"undefined"!=typeof TYPO3.settings.RequireJS.PostInitializationModules&&"undefined"!=typeof TYPO3.settings.RequireJS.PostInitializationModules["TYPO3/CMS/Backend/DragUploader"]&&c.each(TYPO3.settings.RequireJS.PostInitializationModules["TYPO3/CMS/Backend/DragUploader"],function(b,c){a([c])})},b.initialize()});