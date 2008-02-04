tinyMCE.importPluginLanguagePack('posttopostlinks', 'en');

var TinyMCE_posttopostlinksPlugin = {
	getInfo : function() {
		return {
			longname : 'Post-to-Post Links Plugin',
			author : 'Scott Reilly',
			authorurl : 'http://www.coffee2code.com',
			infourl : 'http://www.coffee2code.com/wp-plugins',
			version : '2'
		};
	},

	getControlHTML : function(control_name) {
		switch (control_name) {
			case "posttopostlinks":
				return tinyMCE.getButtonHTML(control_name, 'Post-to-Post Links', '{$pluginurl}/images/post2postlink.png', 'mceposttopostlinks');
		}
		return '';
	},

	execCommand : function(editor_id, element, command, user_interface, value) {
		switch (command) {
			case "mceposttopostlinks":
				var inst = tinyMCE.getInstanceById(editor_id);
				var doc = inst.getDoc();
				var selectedText = "";

				if (tinyMCE.isMSIE) {
					var rng = doc.selection.createRange();
					selectedText = rng.text;
				} else
					selectedText = inst.getSel().toString();

				html = '[post=""';
				if (selectedText != '') {
					html += ' text="' + selectedText + '"';
				}
				html += ']';
				tinyMCE.execInstanceCommand(editor_id, 'mceInsertContent', false, html);
				tinyMCE.selectedInstance.repaint();
				return true;
		}

		// Pass to next handler in chain
		return false;
	}
};

tinyMCE.addPlugin("posttopostlinks", TinyMCE_posttopostlinksPlugin);