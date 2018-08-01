/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


var cx_json = {};
var cx_settings = {};
var cx_records = {};
var cx_customset = {};
var cx_predefined = {};
var cx_positions = {};
var cx_zones = {};
var cx_pages = {};
var cx_uploads = {};
var cx_active_page = 0;
var cx_apps = {};


jQuery(document).ready(function () {
    cunifyadx.initialize();
    cunifyadx.loadDesign();
});

cunifyadx = {

    popoverTitle: 'Code',
    popoverBGColor: 'bg-primary',
    scrollPosition: 0,
    disablePositionSave: false,
    activeDataImg: null,
    twigCodeMirror: null,
    cssCodeMirror: null,
    jsCodeMirror: null,
    jsonCodeMirror: null,
    settingsCodeMirror: null,
    recordsCodeMirror: null,
    customsetCodeMirror: null,
    predefinedCodeMirror: null,
    positionsCodeMirror: null,
    zonesCodeMirror: null,
    elementType: 'html',
    disableAutoThumbnail: 0,
    newThumbnailUploaded: false,
    newThumbnailUploadedName: '',
    newThumbnailUploadContent: {},
    newImageUploaded: false,
    newImageUploadedName: '',
    newImageUploadContent: {},
    measurables: ["cx", "font-size", "line-height", "height", "height", "width", "max-height", "min-height", "max-width", "min-width", "margin", "padding", 'top', 'bottom', 'left', "right", "border", "border-radius", "border-size", 'border-top-size', 'border-bottom-size', 'border-left-size', "border-right-size"],

    initialize: function () {

        cunifyadx.setEvents();
        cunifyadx.setSettingEvents();
        cunifyadx.setRecordEvents();
        cunifyadx.setDesignerEvents();
        cunifyadx.setInsertEvents();
        cunifyadx.setImagesEvents();
        cunifyadx.setPageEvents();
        cunifyadx.setCodeEditor();
        cunifyadx.setTextEditor();



    }, sleep: function (milliseconds) {

        var start = new Date().getTime();

        for (var i = 0; i < 1e7; i++) {
            if ((new Date().getTime() - start) > milliseconds) {
                break;
            }
        }

    }, setEvents: function () {

        cunifyadx.setPopover();

        jQuery('#dataSettingFieldsModal').on('hidden.bs.modal', function () {
            jQuery('#accordion_setting_fields').collapse('hide');
        });
        jQuery('#dataSettingFieldsModal').on('shown.bs.modal', function () {
            jQuery('#accordion_setting_fields').collapse('show');
        });

        jQuery('img.cx-css-setting, button.cx-css-setting, div.cx-css-setting').on('click', function (e) {
            cunifyadx.setCssProperty(jQuery(this), false);
        });

        jQuery('input.cx-css-setting, select.cx-css-setting').on('change', function (e) {
            cunifyadx.setCssProperty(jQuery(this), true);
        });

        jQuery('body, #cunifyadx-designer *').on('click', function (e) {
            jQuery('[data-toggle="popover"]').each(function () {
                //the 'is' for buttons that trigger popups
                //the 'has' for icons within a button that triggers a popup
                if (!jQuery(this).is(e.target) && jQuery(this).has(e.target).length === 0 && jQuery('.popover').has(e.target).length === 0) {
                    jQuery(this).popover('hide');
                }
            });
        });

        jQuery("section").addClass('cx-section');

        jQuery("#cunifyadx-designer").resizable({
            grid: 50
        });


    }, setCssProperty: function (this_element, is_input) {

        var csskey = this_element.data('csskey');
        var cssvalue = this_element.data('cssvalue');

        if (is_input) {
            cssvalue = this_element.val();
        }

        var selected = jQuery('.cunifyadx-designer .cunifyadx-selected');

        if (selected.length) {

            var parent_path = selected.parentsUntil('.cunifyadx-designer');
            var select_str = cunifyadx.getSelectedPath(parent_path, selected);
            var property_object = {csskey};

            if (cx_json.hasOwnProperty(select_str)) {
                delete cx_json[select_str][csskey];
                cx_json[select_str][csskey] = cssvalue;
            } else {
                cx_json[select_str] = {};
                cx_json[select_str][csskey] = cssvalue;
            }

            if (cssvalue === '' || cssvalue == 'undefined') {
                delete cx_json[select_str][csskey];
                return;
            }

            if (csskey === 'border-measure') {
                cx_json[select_str]['border-top-measure'] = cssvalue;
                cx_json[select_str]['border-bottom-measure'] = cssvalue;
                cx_json[select_str]['border-left-measure'] = cssvalue;
                cx_json[select_str]['border-right-measure'] = cssvalue;
            }

            if (csskey === 'positioning') {
                cx_json[select_str]['top-measure'] = cssvalue;
                cx_json[select_str]['bottom-measure'] = cssvalue;
                cx_json[select_str]['left-measure'] = cssvalue;
                cx_json[select_str]['right-measure'] = cssvalue;
            }

            if (csskey === 'id') {
                cunifyadx.changeCssId(select_str, csskey, cssvalue);
            }

            cunifyadx.jsonCodeMirror.setValue(JSON.stringify(cx_json, null, 2));

            cunifyadx.updateCssWithJson();
        }
    }, changeCssId: function (select_str, csskey, cssvalue) {

        var twig_content = cunifyadx.twigCodeMirror.getValue();
        var twig_content_obj = jQuery('<div>' + twig_content + '</div>');
        var selected_obj = jQuery(select_str);
        var selected_obj_id = selected_obj.prop('id');

        selected_obj.prop(csskey, cssvalue);
        twig_content_obj.find(select_str).prop(csskey, cssvalue);
        cunifyadx.twigCodeMirror.setValue(twig_content_obj.html());

        jQuery.each(cx_json, function (key, object) {

            var curr_key = key.replace(selected_obj_id, cssvalue);
            var selected = jQuery(curr_key);

            var parent_path = selected.parentsUntil('.cunifyadx-designer');
            var select_str = cunifyadx.getSelectedPath(parent_path, selected);

            cx_json[select_str] = cx_json[key];

            if (select_str !== key) {
                delete cx_json[key];
            }
        });

    }, updateCssWithJson: function () {

        var css_str = '';
        var total_json = Object.keys(cx_json).length;

        if (total_json) {

            jQuery.each(cx_json, function (key, object) {

                var has_overlay = false;
                var object_counter = 0;
                var overlay_str = key + ':before{ content: "";position: absolute;left: 0;right: 0;top: 0;bottom: 0;z-index: 5;';

                css_str += key + '{\n';
                jQuery.each(object, function (tmp_key, property) {

                    object_counter = object_counter + 1;

                    if (tmp_key == 'background-overlay-opacity') {
                        var opacity = 1 * property / 100;
                        has_overlay = true;
                        var overlay_color = (!object.hasOwnProperty('background-overlay-color')) ? object['background-overlay-color'] : 'white';
                        overlay_str = overlay_str + 'background: ' + overlay_color + ';opacity: ' + opacity + ';';
                    } else if (tmp_key == 'background-overlay-color') {
                    }

                    if (tmp_key.indexOf("measure") >= 0) {
                        // Proceed 
                    } else {
                        if (jQuery.inArray(tmp_key, cunifyadx.measurables) > 0) {

                            var measurement = 'px';
                            if (object.hasOwnProperty(tmp_key + '-measure')) {
                                measurement = object[tmp_key + '-measure'];
                            }

                            css_str += tmp_key + ':' + property + measurement + ";\n";
                        } else {
                            css_str += tmp_key + ':' + property + ";\n";
                        }
                    }
                });

                css_str += '}\n';
                overlay_str = overlay_str + '}';
                overlay_str = overlay_str + key + ' > * {position:relative; z-index:8;}';

                if (has_overlay) {
                    css_str += overlay_str;
                }
            });

            css_str += cunifyadx.cssCodeMirror.getValue();

            jQuery('#cx_design_style').text(css_str);
        }
    }, getSelectedPath: function (parent_path, selected) {

        var elements = [];

        var select_str = '';

        parent_path.each(function (index) {
            elements.push(cunifyadx.getElementNameNID(jQuery(this)))
        });

        elements.reverse();

        elements.push(cunifyadx.getElementNameNID(selected));

        select_str = elements.join(' ');

        return select_str;

    }, getElementNameNID: function (curr_element) {

        var spacer = '';
        var select_str = '';
        var id = curr_element.prop('id');
        var tag_name = curr_element.get(0).tagName;

        if (id != '') {
            select_str = tag_name.toLowerCase() + '#' + id;
        } else {
            select_str = tag_name.toLowerCase();
        }

        return select_str;

    }, setPopover: function (this_element) {

        this_element = this_element || jQuery(document);

        this_element.find('.cunifyadx-popover').popover({
            container: 'body',
            html: true,
            placement: function (context, src) {
                jQuery(context)
                        .removeClass('popover-bg-primary')
                        .removeClass('popover-bg-dark')
                        .removeClass('popover-bg-warning')
                        .addClass('popover-' + cunifyadx.popoverBGColor);
                return 'top';
            },

            content: function () {

                var random_no = Math.round(Math.random() * 100);
                var clone = jQuery(jQuery(this).data('popover-content')).clone(true).removeClass('d-none');

                console.log(jQuery(this).data('popover-content'));
                console.log(clone);

                clone.find('.cx-modify-id').each(function () {

                    var element_id = jQuery(this).prop('id');
                    var element_href = jQuery(this).prop('href');
                    var element_target = jQuery(this).data('target-id');
                    var element_aria_controls = jQuery(this).prop('aria-controls');

                    if (typeof element_href !== typeof undefined && element_href !== false) {
                        if (element_href.charAt(0) === '#' && element_href.length > 1) {
                            jQuery(this).prop('href', element_href + random_no);
                        }
                    }

                    if (typeof element_target !== typeof undefined && element_target !== false) {
                        jQuery(this).data('target-id', element_target + random_no);
                    }

                    if (typeof element_id !== typeof undefined && element_id !== false) {
                        jQuery(this).prop('id', element_id + random_no);
                        console.log(element_id + random_no);
                    }

                    if (typeof element_aria_controls !== typeof undefined && element_aria_controls !== false) {
                        jQuery(this).prop('aria-controls', element_aria_controls + random_no);
                    }
                });



                return clone;
            }

        }).click(function (e) {
            e.preventDefault();
        })
                ;

    }, showAlert: function (msg, type, delay) {

        delay = delay || 10000;

        var alert_clone = jQuery('.cx-alert-wrapper').clone(true);
        alert_clone.removeClass('cx-alert-wrapper')
                .removeClass('d-none')
                .removeClass('alert-primary')
                .removeClass('alert-secondary')
                .removeClass('alert-success')
                .removeClass('alert-danger')
                .removeClass('alert-warning')
                .removeClass('alert-info')
                .removeClass('alert-light')
                .removeClass('alert-dark')
                .addClass('cx-alert').addClass('alert-' + type);
        alert_clone.find('.cx-alert-message').html(msg);

        jQuery('body').append(alert_clone);

        setTimeout(function () {
            jQuery('.cx-alert').hide('slow', function () {
                jQuery('.cx-alert').remove();
            });
        }, delay);


    }, setCodeEditor: function () {

        cunifyadx.twigCodeMirror = CodeMirror.fromTextArea(document.getElementById("cx_code_twig_textarea"), {
            mode: "twig",
            lineNumbers: true,
            autoRefresh: true,
            theme: 'cobalt',
        });

        cunifyadx.cssCodeMirror = CodeMirror.fromTextArea(document.getElementById("cx_code_css_textarea"), {
            mode: "css",
            lineNumbers: true,
            autoRefresh: true,
            theme: 'cobalt',
        });

        cunifyadx.jsCodeMirror = CodeMirror.fromTextArea(document.getElementById("cx_code_js_textarea"), {
            mode: "javascript",
            lineNumbers: true,
            autoRefresh: true,
            theme: 'cobalt',
        });

        cunifyadx.jsonCodeMirror = CodeMirror.fromTextArea(document.getElementById("cx_code_json_textarea"), {
            mode: "javascript",
            lineNumbers: true,
            autoRefresh: true,
        });

        cunifyadx.settingsCodeMirror = CodeMirror.fromTextArea(document.getElementById("cx_code_settings_textarea"), {
            mode: "javascript",
            lineNumbers: true,
            autoRefresh: true,
        });

        cunifyadx.recordsCodeMirror = CodeMirror.fromTextArea(document.getElementById("cx_code_records_textarea"), {
            mode: "javascript",
            lineNumbers: true,
            autoRefresh: true,
        });

        cunifyadx.customsetCodeMirror = CodeMirror.fromTextArea(document.getElementById("cx_code_customset_textarea"), {
            mode: "javascript",
            lineNumbers: true,
            autoRefresh: true,
        });

        cunifyadx.predefinedCodeMirror = CodeMirror.fromTextArea(document.getElementById("cx_code_predefined_textarea"), {
            mode: "javascript",
            lineNumbers: true,
            autoRefresh: true,
        });

        cunifyadx.positionsCodeMirror = CodeMirror.fromTextArea(document.getElementById("cx_code_positions_textarea"), {
            mode: "javascript",
            lineNumbers: true,
            autoRefresh: true,
        });
        
        cunifyadx.zonesCodeMirror = CodeMirror.fromTextArea(document.getElementById("cx_code_zones_textarea"), {
            mode: "javascript",
            lineNumbers: true,
            autoRefresh: true,
        });

    }, setTextEditor: function () {

        tinymce.init({
            selector: '.texteditor',
            plugins: [
                'advlist autolink lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table contextmenu paste'
            ],
            toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',

        });

    }
};



/*
 tinymce.init({
 selector: '.cunifyadx-designer h1,.cunifyadx-designer h2,.cunifyadx-designer h3,.cunifyadx-designer h4,.cunifyadx-designer h5,.cunifyadx-designer h6',
 inline: true,
 toolbar: 'undo redo',
 menubar: false
 });
 
 tinymce.init({
 selector: '.cunifyadx-designer ul,.cunifyadx-designer ol',
 inline: true,
 toolbar: 'undo redo',
 menubar: false
 });
 
 tinymce.init({
 selector: '.cunifyadx-designer p',
 inline: true,
 menubar: false,
 plugins: [
 'advlist autolink lists link image charmap print preview anchor',
 'searchreplace visualblocks code fullscreen',
 'insertdatetime media table contextmenu paste'
 ],
 toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
 
 });
 */
