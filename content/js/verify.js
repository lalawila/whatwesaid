(function(){
if(document.all) {
    window.attachEvent('onload', init_verify);
}
else {
    window.addEventListener('load', init_verify, false);
}

function init_verify() {

    if(document.all) {
        document.getElementById("sumbit-article-form").attachEvent('onsubmit',article_submit);
    }
    else {
        document.getElementById("sumbit-article-form").addEventListener('submit', article_submit, false);
    }
}

function article_submit(e) {
    e.preventDefault();
    
    if(this.title.value == '') {
        alert('title cant be empty');
        return;
    }
    if(this.author.value == '') {
        alert('author cant be empty');
        return;
    }
    if(this.content.value == '') {
        alert('content cant be empty');
        return;
    }
    if(this.original.value == '') {
        alert('original cant be empty');
        return;
    }
    this.submit();
}

})();
