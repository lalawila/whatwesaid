CKEDITOR.plugins.add( 'myimage', {
    icons: '',
    init: function( editor ) {
        // Do not execute this paste listener if it will not be possible to upload file.
        if ( !CKEDITOR.plugins.clipboard.isFileApiSupported ) {
            return;
        }

        var fileTools = CKEDITOR.fileTools,
            uploadUrl = fileTools.getUploadUrl( editor.config, 'image' );

        if ( !uploadUrl ) {
            CKEDITOR.error( 'uploadimage-config' );
            return;
        }       

        // Handle images which are available in the dataTransfer.
        fileTools.addUploadWidget( editor, 'uploadimage', {
            supportedTypes: /image\/(jpeg|png|gif|bmp)/,

            uploadUrl: uploadUrl,

            fileToElement: function() {
                var img = new CKEDITOR.dom.element( 'img' );
                img.setAttribute( 'src', loadingImage );
                return img;
            },

            parts: {
                img: 'img'
            },

            onUploading: function( upload ) {
                // Show the image during the upload.
                this.parts.img.setAttribute( 'src', upload.data );
            },

            onUploaded: function( upload ) {
                // Width and height could be returned by server (#13519).
                var $img = this.parts.img.$,
                    width = upload.responseData.width || $img.naturalWidth,
                    height = upload.responseData.height || $img.naturalHeight;

                // Set width and height to prevent blinking.
                this.replaceWith( '<img src="' + upload.url + '" ' +
                    'width="' + width + '" ' +
                    'height="' + height + '">' );
            }
        } );

        editor.addCommand( 'upload', {
            exec: function( editor ) {
                var now = new Date();
                editor.insertHtml( 'The current date and time is: <em>' + now.toString() + '</em>' );
                if( typeof(inputfile) == "undefined"){
                    abbs = CKEDITOR.document.getById("cke_article");
                    var inputfile = new CKEDITOR.dom.element( 'input' );
                    inputfile.setAttributes( {
                        id:'inputfile',
                        type:'file',
                        accept:'image/jpg,image/jpeg,image/png',
                        style:'display:none;'
                    });
                    inputfile.on('change', function(event){
                        alert(inputfile.getValue());
                        CKEDITOR.ajax.post( '/image.py','' , null, function( data ) {
                        console.log( data );
                        } );
                    });
                    abbs.append(inputfile);
                }
                document.getElementById('inputfile').click();
            }
        });
        editor.ui.addButton( 'Timestamp', {
            label: 'Image',
            command: 'upload',
            toolbar: 'insert'
        });
    }
});
var loadingImage = 'data:image/gif;base64,R0lGODlhDgAOAIAAAAAAAP///yH5BAAAAAAALAAAAAAOAA4AAAIMhI+py+0Po5y02qsKADs=';
