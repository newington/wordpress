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

	tinymce.create('tinymce.plugins.MediaCoreChooserPlugin', {

		/**
		 * Initialize the plugin
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, pluginUrl) {

			var t = this;
			t.editor = ed;
			t.url = pluginUrl;

			t.btnClass = 'mcore-chooser-image';
			t.defaultWidth = 400;
			t.defaultHeight = 225;
			t.shortcodeStr = '[mediacore height="%height%" public_url="%public_url%" thumb_url="%thumb_url%" title="%title%" width="%width%"]';
			t.shortcodeRegex = /\[mediacore height="[^"]*" public_url="[^"]*" thumb_url="[^"]*" title="[^"]*" width="[^"]*"\]/gi;
			t.imageStr = '<img class="mcore-chooser-image" alt="%alt%" src="%src%" data-public-url="%public_url%" width="%width%" height="%height%" data-mce-placeholder="1" />';
			t.imageRegex = /<img class="mcore-chooser-image"[^>]*>/gi;
			t.widthRegex = /width=\"[0-9]+\"/gi;
			t.heightRegex = /height=\"[0-9]+\"/gi;
			t.shortcodes = [];
			t.DOM = tinymce.DOM;

			/**
			 * Init the MediaCore Chooser
			 */
			t.loadScript(t.editor.getParam('mcore_chooser_js_url'));
			var params = {
				'url': t.editor.getParam('mcore_url'),
				'mode': 'popup'
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
						replace(/%title%/, media.title).
						replace(/%width%/, t.defaultWidth).
						replace(/%height%/, t.defaultHeight);
					t.editor.execCommand('mceInsertContent', false, sc);
					});
					t.chooser.on('error', function(err) {
						throw err;
					});
				}
				t.chooser.open();
			});

			/**
			 * Add the MediaCore button to TinyMCE
			 */
			t.editor.addButton('mediacore', {
				title: 'MediaCore Chooser',
				image: t.url + '/images/mcore-tinymce-icon.png',
				cmd: 'mceMediaCoreChooser'
			});

			/**
			 * Creates custom image edit buttons that replace
			 * the default wpimageedit image toolbar behaviour
			 */
			t.editor.on('LoadContent', function(e) {
				var editor = e.target;
				t.createImageToolbar(editor);
			});

			/**
			 * Editor mousedown event listener/handler
			 */
			t.editor.on('mousedown', function(e) {
				var target = e.target;
				t.hideImageToolbar();
				if (target.nodeName.toUpperCase() == 'IMG') {
					if (t.DOM.hasClass(target, t.btnClass)) {
						t.showImageToolbar(target);
					}
				}
			});

			/**
			 * Editor change event listener/handler
			 */
			t.editor.on('change', function(e) {
				t.hideImageToolbar();
				if (!t.shortcodeRegex.test(e.content)) {
					return;
				}
				e.content = t.replaceShortcodes(e.content);
				t.editor.setContent(e.content);
				t.editor.execCommand('mceRepaint');
			});

			/**
			 * Editor BeforeSetContent event listener/handler
			 */
			t.editor.on('BeforeSetContent', function(e) {
				t.hideImageToolbar();
				if (t.shortcodeRegex.test(e.content)){
					e.content = t.replaceShortcodes(e.content);
				}
				return;
			});

			/**
			 * Editor postprocess event listener/handler
			 */
			t.editor.on('PostProcess', function(e) {
				t.hideImageToolbar();
				if (e.get) {
					e.content = t.replaceImages(e.content);
				}
			});
		},

		/**
		 * Append a new script tag to the document body
		 * @param {string} url
		 */
		loadScript: function(url) {
			var script = document.createElement('script');
			script.src = url;
			(document.body || document.head || document.documentElement).appendChild(script);
		},

		/**
		 * Replace embed shortcodes with the IMG html
		 * @param {string} content
		 * @return {string}
		 */
		replaceShortcodes: function(content) {
			this.shortcodes = content.match(this.shortcodeRegex);
			var el, imgHtml;
			for (var i = 0, code; code = this.shortcodes[i]; ++i) {
				//determine width and height based on the image
				var width = this.getAttrValueFromStr('width', code) || t.defaultWidth;
				var height = this.getAttrValueFromStr('height', code) || t.defaultHeight;
				//update the img markup
				imgHtml = this.imageStr.
					replace(/%alt%/, tinymce.DOM.encode(
						this.getAttrValueFromStr('title', code))).
					replace(/%src%/, this.getAttrValueFromStr('thumb_url', code)).
					replace(/%public_url%/, this.getAttrValueFromStr('public_url', code)).
					replace(/%width%/, width).
					replace(/%height%/, height);
				el = document.createElement('div');
				el.innerHTML = imgHtml;
				content = content.replace(code, imgHtml);
			}
			return content;
		},

		/**
		 * Get an attribute value from an HTML string or an embed shortcode
		 * style string
		 * @param {string} attr
		 * @param {string} str
		 * @return {string}
		 */
		getAttrValueFromStr: function(attr, str) {
			var re = new RegExp(attr + '="([^"]*)"', 'i');
			var result = re.exec(str);
			if (!result || !result.length) {
				return '';
			}
			return result[1];
		},

		/**
		 * Replace image html with embed shortcodes
		 * @param {string} content
		 * @return {string}
		 */
		replaceImages: function(content) {
			var shortcode;
			this.imageRegex.lastIndex = 0;
			var images = content.match(this.imageRegex) || [];
			for (var i = 0, img; img = images[i]; ++i) {

				//determine width and height based on the image
				var width = this.getAttrValueFromStr('width', img) || t.defaultWidth;
				var height = this.getAttrValueFromStr('height', img) || t.defaultHeight;

				//update the shortcode string
				shortcode = this.shortcodeStr.
					replace(/%public_url%/, this.getAttrValueFromStr('data-public-url', img)).
					replace(/%thumb_url%/, this.getAttrValueFromStr('src', img)).
					replace(/%title%/, this.getAttrValueFromStr('alt', img)).
					replace(/%width%/, width).
					replace(/%height%/, height);
				content = content.replace(img, shortcode);
			}
			return content;
		},

		/**
		 * Create a custom edit image toolbar with a delete
		 * and edit button.
		 * This markup matches that used by the wpeditimage plugin and is reliant
		 * on wp-3.9 dashicons:
		 * {@link http://make.wordpress.org/core/2014/04/16/dashicons-in-wordpress-3-9/}
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 */
		createImageToolbar: function(editor) {
			var t = this;
			var dom = tinymce.DOM;

			var toolbarElem = dom.get('mcore-image-toolbar');
			if (!toolbarElem) {
				toolbarElem = dom.create('p', {
					'id': 'mcore-image-toolbar'
				});
				dom.setAttribs(toolbarElem, {
					'data-mce-bogus': '1',
					'contenteditable': 'false'
				});
				var editBtnElem = dom.create('i', {
					'id': 'mcore-edit-button',
					'class': 'dashicons dashicons-edit edit'
				});
				dom.setAttribs(editBtnElem, {
					'data-mce-bogus': '1'
				});
				var delBtnElem = dom.create('i', {
					'id': 'mcore-delete-button',
					'class': 'dashicons dashicons-no-alt remove'
				});
				dom.setAttribs(delBtnElem, {
					'data-mce-bogus': '1'
				});

				dom.add(toolbarElem, editBtnElem);
				dom.add(toolbarElem, delBtnElem);
				dom.add(editor.getBody(), toolbarElem);
				t.toolbarElem = toolbarElem;

				// Edit button mousedown listener/handler
				dom.bind(editBtnElem, 'mousedown', function(e) {
					t.hideImageToolbar();
					t.editor.execCommand('mceMediaCoreChooser');
					return;
				});

				// Delete button mousedown listener/handler
				dom.bind(delBtnElem, 'mousedown', function(e) {
					var el = t.editor.selection.getNode();
					if (el.nodeName == 'IMG' && t.DOM.hasClass(el, t.btnClass)) {
						t.hideImageToolbar();
						t.DOM.remove(el);
						t.editor.execCommand('repaint');
						return;
					}
				});
			}
		},

		/**
		 * Show the image edit/del toolbar
		 * @param {Element}
		 */
		showImageToolbar: function(target) {
			if (this.toolbarElem) {
				this.toolbarElem.style.top = target.offsetTop + 'px';
				this.toolbarElem.style.left = target.offsetLeft + 'px';
				this.toolbarElem.style.display = 'block';
			}
		},

		/**
		 * Hide the image edit/del toolbar
		 */
		hideImageToolbar: function() {
			if (this.toolbarElem) {
				this.toolbarElem.style.display = 'none';
			}
		},

		/**
		 * get the MediaCore Chooser plugin info
		 * @return {object}
		 */
		getInfo: function() {
			return {
				author : 'MediaCore <info@mediacore.com>',
				authorurl: 'http://mediacore.com',
				longname : 'MediaCore Chooser',
				version : '2.7.2'
			};
		}
	});

	tinymce.PluginManager.add('mediacore', tinymce.plugins.MediaCoreChooserPlugin);
})();
