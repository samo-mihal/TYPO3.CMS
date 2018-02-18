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
define(["require","exports","./Enum/Severity","./Severity"],function(a,b,c,d){"use strict";var e,f=function(){function a(){}return a.notice=function(b,d,e){a.showMessage(b,d,c.SeverityEnum.notice,e)},a.info=function(b,d,e){a.showMessage(b,d,c.SeverityEnum.info,e)},a.success=function(b,d,e){a.showMessage(b,d,c.SeverityEnum.ok,e)},a.warning=function(b,d,e){a.showMessage(b,d,c.SeverityEnum.warning,e)},a.error=function(b,d,e){void 0===e&&(e=0),a.showMessage(b,d,c.SeverityEnum.error,e)},a.showMessage=function(a,b,e,f){void 0===f&&(f=this.duration);var g=d.getCssClass(e),h="";switch(e){case c.SeverityEnum.notice:h="lightbulb-o";break;case c.SeverityEnum.ok:h="check";break;case c.SeverityEnum.warning:h="exclamation";break;case c.SeverityEnum.error:h="times";break;case c.SeverityEnum.info:default:h="info"}f="undefined"==typeof f?this.duration:"string"==typeof f?parseFloat(f):f,null===this.messageContainer&&(this.messageContainer=$('<div id="alert-container"></div>').appendTo("body"));var i=$('<div class="alert alert-'+g+' alert-dismissible fade" role="alert"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true"><i class="fa fa-times-circle"></i></span><span class="sr-only">Close</span></button><div class="media"><div class="media-left"><span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-'+h+' fa-stack-1x"></i></span></div><div class="media-body"><h4 class="alert-title"></h4><p class="alert-message text-pre-wrap"></p></div></div></div>');i.find(".alert-title").text(a),i.find(".alert-message").text(b),i.on("close.bs.alert",function(a){a.preventDefault();var b=$(a.currentTarget);b.clearQueue().queue(function(a){b.removeClass("in"),a()}).slideUp(function(){b.remove()})}),i.appendTo(this.messageContainer),i.delay(200).queue(function(a){i.addClass("in"),a()}),f>0&&i.delay(1e3*f).queue(function(a){i.alert("close"),a()})},a.duration=5,a.messageContainer=null,a}();try{parent&&parent.window.TYPO3&&parent.window.TYPO3.Notification&&(e=parent.window.TYPO3.Notification),top&&top.TYPO3.Notification&&(e=top.TYPO3.Notification)}catch(a){}return e||(e=f,"undefined"!=typeof TYPO3&&(TYPO3.Notification=e)),e});