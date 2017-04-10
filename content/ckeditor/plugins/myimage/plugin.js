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
                        var path = this.value;
                        if( path != '') {

                            var files = this.files;
                            for(var i in files){
                                if( files[0].size/1024 > 200){
                                    alert("max size 200Kb");
                                    this.value = '';
                                    return ;
                                }
                            }

                            if ( window.FormData !== undefined){
                                var xmlhttp = new XMLHttpRequest();
                                xmlhttp.open("POST", "image.py", true);
                                var image = new FormData(document.forms.namedItem("imagefile"));
                                xmlhttp.onload = function( event ) {
                                    if(this.status == 200) {
                                        editor.insertHtml('<img style="max-width:100%;" src="' + xmlhttp.responseText + '">', 'unfiltered_html');
                                    }
                                    else
                                        alert('error');
                                
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
