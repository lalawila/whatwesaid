CKEDITOR.plugins.add( 'myimage', {
    icons: 'image',
    init: function( editor ) {
        editor.addCommand( 'upload', {
            exec: function( editor ) {
                if( typeof(inputfile) == "undefined" ){
                    abbs = CKEDITOR.document.getById("cke_article");
                    abbs.appendHtml('<form enctype="multipart/form-data" method="post" name="imagefile">'+
                                    '<input id="inputfile" name="inputfile" type="file" multiple=""'+ 
                                    'accept="image/jpg,image/jpeg,image/png" style="display: none;">'+
                                    '</form>');
                    inputfile = document.getElementById('inputfile');
                    inputfile.onchange = function(event){
                        var path = inputfile.value;
                        if( path != '') {
                            if ( window.FormData !== undefined){
                                var image = new FormData(document.forms.namedItem("imagefile"));
                                var xmlhttp = new XMLHttpRequest();
                                xmlhttp.open("POST", "image.py", true);
                                xmlhttp.onload = function( event ) {
                                    if(this.status == 200) {
                                        editor.insertHtml('<img style="max-width:100%;" src="' + xmlhttp.responseText + '">', 'unfiltered_html');
                                    }
                                    else
                                        alert('fuck');
                                
                                }
                                xmlhttp.send(image);
                                
                            }
                        }
                    };
                }
                inputfile.click();
            }
        });
        editor.ui.addButton( 'image', {
            label: 'Image',
            command: 'upload',
            toolbar: 'insert'
        });
    }
});
var loadingImage = 'data:image/gif;base64,R0lGODlhDgAOAIAAAAAAAP///yH5BAAAAAAALAAAAAAOAA4AAAIMhI+py+0Po5y02qsKADs=';
