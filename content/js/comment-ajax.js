(function(){

    $(document).ready(function(){
        if( !window.console) window.console = {};
        if( !window.console.log) window.console.log = function() {};

        $("#comment-area").on("submit",function() {
            new_comment($(this));         
            return false;
        });
        $("#comment").select();
        updater.poll();
    });

    function new_comment(form){
        var json = {};
        json["comment"] = form.children("#comment").val();
        json["article"] = $("#comment-area input[name='article']").val();
        form.disable();
        $.put_json("/api/comment", json, function(response){
            form.enable();
        });  
    }

    jQuery.put_json = function( url, args, callback ) {

        $.ajax({url: url ,data: $.param(args), dataType:"text", type: "PUT", 
            success: function(response) {
                if( callback ) callback(eval("(" + response + ")" ));
        }, error: function(response) {
            alert("error");
        }});
    
    };

    jQuery.fn.disable = function() {
        this.enable(false);
        return this;
    };

    jQuery.fn.enable = function(opt_enable) {
        if ( arguments.length && !opt_enable ) {
            this.attr("disabled", "disabled");
        }
        else {
            this.removeAttr("disabled");
        }
        return this;
    
    };

    var updater = {
        errorSleepTime: 500,
        cursor: null, 

        poll: function() {
            var args = { "article" : $("#comment-area input[name='article']").val()};
            if( updater.cursor ) args.cursor = updater.cursor;
            $.ajax({ url:"/api/comment", type: "GET", dataType: "text",
                data: $.param(args), success: updater.onSuccess, error: updater.onError });
        },

        onSuccess: function(response) {
            try {
                updater.newComments(eval( "(" + response + ")"));
            } catch (e) {
                updater.onError();
                return;            
            }
            updater.errorSleepTime = 500;
            window.setTimeout( updater.poll, 0);
        },

        onError: function(response) {
            updater.errorSleepTime *= 2;
            console.log("Poll error; sleeping for", updater.errorSleepTime, "ms");
            window.setTimeout(updater.poll, updater.errorSleepTime);
        },
    
        newComments: function(response) {
            if (!response.comments || response.comments.length == 0) return;
            var comments = response.comments;
            updater.cursor = comments[comments.length - 1].time;
            for( var i = 0; i < comments.length; i++) {
                updater.showComment(comments[i] );
            }
        }, 
        
        showComment: function(comment) {
            var node = updater.wrapComment(comment);
            node.hide();
            $("#inbox").append(node);
            node.slideDown(); 
        },

        wrapComment: function(comment) {
            var container = $("<div/>");
            var time = new Date(comment.time * 1000);
            container.append( $("<div>" + time.toLocaleString() + "</div>") );
            container.append( $("<div>" + comment.comment + "</div>") );
            return container;
        }
    };

})();
