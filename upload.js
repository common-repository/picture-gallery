// VideoShareVOD.com Multi Drag & Drop Picture Upload Script
// Developed by VideoWhisper.com

(function() {

	function vwEBI(id) {
		return document.getElementById( id );
	}

	function vwO(msg) {
		var m       = vwEBI( "messages" );
		m.innerHTML = msg + m.innerHTML;
	}

	function vwFDH(e) {
		e.stopPropagation();
		e.preventDefault();
		e.target.className = (e.type == "dragover" ? "hover" : "");
	}

	function vwFSH(e) {
		vwFDH( e );
		var files = e.target.files || e.dataTransfer.files;
		for (var i = 0, f; f = files[i]; i++) {
			vwUF( f );
		}
	}

	function vwUF(file) {
		var xhr = new XMLHttpRequest();
		if (xhr.upload && (file.type.indexOf( "image" ) == 0) && file.size > 0 && file.size <= vwEBI( "MAX_FILE_SIZE" ).value) {
			var o        = vwEBI( "progress" );
			var progress = o.appendChild( document.createElement( "div" ) );
			progress.appendChild( document.createTextNode( encodeURIComponent( file.name ) + " Uploading... " ) );
			var msg = "MIME Type: " + file.type + " Size: " + file.size + " bytes";
			if (file.type.indexOf( "image" ) != 0) {
				msg = msg + " Type not allowed: Image mime required!";
			}
			progress.appendChild( document.createTextNode( msg ) );

			var pSpan         = progress.appendChild( document.createElement( "span" ) );
			pSpan.style.width = "1%";

			xhr.upload.addEventListener(
				"progress",
				function(e) {
					pSpan.style.width = parseInt( e.loaded / e.total * 100 ) + "%";
				},
				false
			);
			xhr.onreadystatechange = function(e) {
				if (xhr.readyState == 4) {
					progress.className = (xhr.status == 200 ? "success" : "failure");

					if (xhr.responseText != null) {
						var pP       = progress.appendChild( document.createElement( "p" ) );
						pP.innerHTML = xhr.responseText;
					}
				}
			};

			xhr.open( "POST", vwEBI( "upload" ).action, true );
			xhr.setRequestHeader( "X-FILENAME", file.name.replace( /(<([^>]+)>)/ig,"" ) );
			xhr.setRequestHeader( "X-CATEGORY", vwEBI( 'category' ).value.replace( /(<([^>]+)>)/ig,"" ) );
			xhr.setRequestHeader( "X-OWNER", vwEBI( 'owner' ).value.replace( /(<([^>]+)>)/ig,"" ) );
			xhr.setRequestHeader( "X-DEV", 'Uploaded with VideoShareVOD.com script by VideoWhisper.com' );
			xhr.setRequestHeader( "X-GALLERY", vwEBI( 'gallery' ).value.replace( /(<([^>]+)>)/ig,"" ) );
			xhr.setRequestHeader( "X-TAG", vwEBI( 'tag' ).value.replace( /(<([^>]+)>)/ig,"" ) );
			xhr.setRequestHeader( "X-DESCRIPTION", vwEBI( 'description' ).value.replace( /(<([^>]+)>)/ig,"" ) );
			xhr.send( file );
		}

	}

	function vwI() {
		var fileselect   = vwEBI( "fileselect" );
		var	filedrag     = vwEBI( "filedrag" );
		var	submitbutton = vwEBI( "submitbutton" );

		if (fileselect) {
			fileselect.addEventListener( "change", vwFSH, false );
		}

		var xhr = new XMLHttpRequest();

		if (filedrag) {
			if (xhr.upload) {
				filedrag.addEventListener( "dragover", vwFDH, false );
				filedrag.addEventListener( "dragleave", vwFDH, false );
				filedrag.addEventListener( "drop", vwFSH, false );
				filedrag.style.display     = "block";
				submitbutton.style.display = "none";
			}
		}

	}

	if (window.File && window.FileList && window.FileReader) {
		vwI();
	}

})();
