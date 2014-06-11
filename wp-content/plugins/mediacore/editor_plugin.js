/**
 * This borrows ideas from the following excellent shortcodes plugin:
 * Plugin Name: Visual Shortcodes
 * Plugin URI: http://wordpress.org/extend/plugins/visual-shortcodes
 *
 * Copyright 2012  MediaCore  (email: info@mediacore.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

(function() {

  /**
   * Append a new script url to the bottom of the
   * document
   * @param {string} url
   */
  function loadScript(url) {
    var script = document.createElement('script');
    script.src = url;
    (document.body || document.head || document.documentElement).appendChild(script);
  }

  tinymce.create('tinymce.plugins.MediaCoreChooserPlugin', {

    /**
     * Chooser Init
     * @param {object} ed
     * @param {string} pluginUrl
     */
    init : function(ed, pluginUrl) {
      var t = this;
      t.editor = ed;
      t.url = pluginUrl;
      t.btnClass = 'mcore-chooser-image';
      t.shortcodeStr = '[mediacore height="315px" public_url="%public_url%" thumb_url="%thumb_url%" title="%title%" width="560px"]';
      t.shortcodeRegex = /\[mediacore height="[^"]*" public_url="[^"]*" thumb_url="[^"]*" title="[^"]*" width="[^"]*"\]/gi;
      t.imageStr = '<img class="mcore-chooser-image" alt="%alt%" src="%src%" data-public-url="%public_url%" />';
      t.imageRegex = /<img class="mcore-chooser-image"[^>]*>/gi;
      t.shortcodes = [];
      t.DOM = tinyMCE.DOM;

      t.editor.on('mousedown', function(e) {
        var target = e.target;
        t._hideButtons();
        if (!t.editor.plugins.wordpress) {
          return;
        }
        if (target.nodeName == 'IMG') {
          if (t.DOM.hasClass(target, t.btnClass)) {
            t.editor.plugins.wordpress._showButtons(target, 'mcore-image-buttons');
          } else {
            t.editor.plugins.wordpress._showButtons(target, 'wp_editbtns');
          }
        }
      });

      /**
       * Creates custom image edit buttons that override
       * the default wordpress edit behaviour
       */
      t._createButtons();

      loadScript(t.editor.getParam('mcore_chooser_js_url'));
      var params = {
        'mcore_host': t.editor.getParam('host'),
        'mcore_scheme': t.editor.getParam('scheme', 'http')
      };

      t.editor.addCommand('mceMediaCoreChooser', function() {
        if (!window.mediacore) {
          t.editor.windowManager.alert('Error loading the MediaCore plugin');
          return;
        }
        if (!t.chooser) {
          t.chooser = mediacore.chooser.init(params);
          t.chooser.on('media', function(media) {
            var sc = t.shortcodeStr.
                replace(/%public_url%/, media.public_url).
                replace(/%thumb_url%/, media.thumb_url).
                replace(/%title%/, media.title);
            t.editor.execCommand('mceInsertContent', false, sc);
          });
          t.chooser.on('error', function(err) {
            throw err;
          });
        }
        t.chooser.open();
      });

      t.editor.addButton('mediacore', {
        title : 'MediaCore Chooser',
        image : t.url + '/images/mcore-tinymce-icon.png',
        cmd : 'mceMediaCoreChooser'
      });

      t.editor.on('BeforeSetContent', function(e) {
        t._hideButtons();
        if (t.shortcodeRegex.test(e.content)){
          e.content = t._replaceShortcodes(e.content);
        }
        return;
      });

      t.editor.on('change', function(e) {
        t._hideButtons();
        if (!t.shortcodeRegex.test(e.content)) {
          return;
        }
        e.content = t._replaceShortcodes(e.content);
        t.editor.setContent(e.content);
        t.editor.execCommand('mceRepaint');
      });

      t.editor.on('PostProcess', function(e) {
        t._hideButtons();
        if (e.get) {
          e.content = t._replaceImages(e.content);
        }
      });
    },

    /**
     * MediaCore Chooser Info
     * @return {object}
     */
    getInfo : function() {
      return {
        longname : 'MediaCore Chooser',
        author : 'MediaCore <info@mediacore.com>',
        authorurl: 'http://mediacore.com',
        version : '2.5a'
      };
    },

    /**
     * Replace shortcodes with image html
     * @param {string} content
     * @return {string}
     */
    _replaceShortcodes: function(content) {
      this.shortcodes = content.match(this.shortcodeRegex);
      var el, imgHtml;
      for (var i = 0, code; code = this.shortcodes[i]; ++i) {
        imgHtml = this.imageStr.
            replace(/%alt%/, tinymce.DOM.encode(
                  this._getAttrValueFromStr('title', code))).
            replace(/%src%/, this._getAttrValueFromStr('thumb_url', code)).
            replace(/%public_url%/, this._getAttrValueFromStr('public_url', code));
        el = document.createElement('div');
        el.innerHTML = imgHtml;
        content = content.replace(code, imgHtml);
      }
      return content;
    },

    /**
     * Get the value from a string with attributes,
     *  typically an html string, but it can be used
     *  for any string that follows the same pattern
     * @param {string} attr
     * @param {string} str
     * @return {string}
     */
    _getAttrValueFromStr: function(attr, str) {
      var re = new RegExp(attr + '="([^"]*)"', 'i');
      var result = re.exec(str);
      if (!result.length) {
        return '';
      }
      return result[1];
    },

    /**
     * Replace image html with shortcodes
     * @param {string} content
     * @return {string}
     */
    _replaceImages: function(content) {
      var shortcode;
      this.imageRegex.lastIndex = 0;
      var images = content.match(this.imageRegex) || [];
      for (var i = 0, img; img = images[i]; ++i) {
        shortcode = this.shortcodeStr.
            replace(/%public_url%/, this._getAttrValueFromStr('data-public-url', img)).
            replace(/%thumb_url%/, this._getAttrValueFromStr('src', img)).
            replace(/%title%/, this._getAttrValueFromStr('alt', img));
        content = content.replace(img, shortcode);
      }
      return content;
    },

    /**
     * Create the image hover edit/delete buttons
     */
    _createButtons: function() {
      var t = this;
      t.DOM.remove('mcore-image-buttons');
      t.DOM.add(document.body, 'div', {
        id: 'mcore-image-buttons',
        style: 'display:none'
      });
      var editBtn = t.DOM.add('mcore-image-buttons', 'img', {
        src: t.url + '/images/edit-btn.png',
        id: 'mcore-edit-button',
        width: '24',
        height: '24',
        style: ''
      });
      var deleteBtn = t.DOM.add('mcore-image-buttons', 'img', {
        src: t.url + '/images/delete-btn.png',
        id: 'mcore-delete-button',
        width: '24',
        height: '24',
        style: ''
      });

      t.DOM.bind(editBtn, 'mousedown', function(e) {
        t._hideButtons();
        t.editor.execCommand('mceMediaCoreChooser');
        return false;
      });
      t.DOM.bind(deleteBtn, 'mousedown', function(e) {
        var el = t.editor.selection.getNode();
        if (el.nodeName == 'IMG' && t.DOM.hasClass(el, t.btnClass)) {
          t._hideButtons();
          t.DOM.remove(el);
          t.editor.execCommand('repaint');
          return false;
        }
      });
    },

    /**
     * Hide the edit/delete buttons
     */
    _hideButtons: function() {
      this.DOM.hide('mcore-image-buttons');
      this.DOM.hide('wp_editbtns');
    }
  });
  tinymce.PluginManager.add('mediacore', tinymce.plugins.MediaCoreChooserPlugin);
})();
