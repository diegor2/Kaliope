 <html>
    <head>
      <title>Kaliope</title>
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
      <script src="swfobject/swfobject.js"></script>  
        
      <meta property="og:title" content="Kaliope" />
      <meta property="og:type" content="website" />
      <meta property="og:url" content="http://ruggeri.net.br/labs/kaliope/" />
      <meta property="og:image" content="http://ruggeri.net.br/labs/kaliope/sacred-chao.jpg" />

      <style>
        body {
          background-color : #dddddd;
        }
      </style>
    </head>
    <body>
      <script>
        window.fbAsyncInit = function() {
          FB.init({
            appId      : '115254318598686',
            status     : true, 
            cookie     : true,
            xfbml      : true,
            oauth      : true,
          });
          
          window.k = new Kaliope(FB);
          FB.getLoginStatus(k.startWhenConnected);
        };
        
        (function(d){
           var js, id = 'facebook-jssdk'; if (d.getElementById(id)) {return;}
           js = d.createElement('script'); js.id = id; js.async = true;
           js.src = "//connect.facebook.net/en_US/all.js";
           d.getElementsByTagName('head')[0].appendChild(js);
         }(document));
         
        
         
         function Kaliope(FB){
         
           var postList = {};
           var POSTLIMIT = 10;
           var playerLoaded = false;
           
           var Post = function(name, tip, src){
           	this.name = name;
           	this.tip = tip;
           	this.src = src;
           	//this.id = id;
           	//this.from = from;
           };
        
           this.startWhenConnected = function(response){
             startIfConnected(response);
           };
           
           var startIfConnected = function(response){
             if (response.status === 'connected') {
               start();
             } else {
               FB.Event.subscribe('auth.authResponseChange', startIfConnected);
             }
           };
           
           this.login = function(){
            FB.login(function(response) {
 	     // handle the response 
            }, {scope: 'read_stream'});
           };
         
           var getToken = function(){
              var token;
              FB.getLoginStatus(function(response) {
                token = response.authResponse.accessToken;
              });
              return token;
           };
         
           var logout = function(){
             FB.logout(function(response) {
               // user is now logged out
             });
           };
         
           var start = function(){
           	loadFeed(0);
           	//showAllPosts();
           };

           var loadPlayer = function(p){
             //var params = { allowScriptAccess: "always" };
             //var atts = { id: "myytplayer" };
             //swfobject.embedSWF(p.src+'?enablejsapi=1&playerapiid=myytplayer', 
             //                   'videoDiv', '400', '300', '8', null, null, params, atts);
             $('#iframeVideo').attr('src', p.source);
             updateSocial(p);
             playerLoaded  = true;
           };
                  
           var updateSocial = function(p){
             var info = '';
             info += 'Postado por ';
             info += '<strong><a href=\'https://www.facebook.com/'+p.from.id+'\' target=\'_blank\'>';
             info += p.from.name;
             info += '</a></strong>';
             info += ' ';
             info += p.created_time;
             $('#kaliope-social').html(info);
           }
                  
           var showAllPosts = function(){
             for(i in postList){
               var p = posts[i];
               appendPost(p);
             }
           };

          var appendPost = function(p){
             var a = $('<a>');
             var list = $('#kaliope-playlist');
             a.attr('href', p.source);
             a.attr('title', p.description);
             //a.attr('target', '_blank');
             a.attr('target', 'iframeVideo');
             a.click(function(){updateSocial(p)});
             a.append(p.name);
             list.append($('<li/>').append(a));
           };
         
           var loadFeed = function(since){
              //FB.api('/me/home?limit='+POSTLIMIT +'&offset='+paging*POSTLIMIT, function(response) {
              $.getJSON(
                'https://graph.facebook.com/fql?q='+
                'select post_id , created_time, actor_id, attachment from stream where filter_key in'+
                '(select filter_key from stream_filter where uid=me() and type=\'newsfeed\')'
                , function(response) {

                 var posts = response.data;
                 var duplicated = false;
                 var p, id, src;

                 for(id in posts){
                   p = posts[id];
                   src = p.attachment.media.href.video.source_url;
                   if(src && src.search(/youtube/)>0){
                     if(postList[p.post_id]){
                       duplicated = true;
                       break;
                     } else {
                       appendPost(p);
                       if(!playerLoaded){loadPlayer(p);}
                       duplicated = false;
                     }
                     postList[p.post_id] = p;
                   }
                 }
                 
                 paging++;
                 console.log('since ' + since + ' posts ' + posts.length); //+ 'next ' + response.paging.next + 'prev ' + response.paging.previous);
                 
                 if(posts.length && !duplicated){
                   setTimeout(function(){
                     loadFeed(paging);
                   }, 1000);
                 } else {
                   setTimeout(function(){
                     loadFeed(0);
                   }, 30000);
                 }
             });
           }
         }         
      </script>
      
      <div class="fb-login-button" data-show-faces="true" data-width="200" data-max-rows="1" data-scope="read_stream"></div>
      <!--div class="fb-like" data-href="http://ruggeri.net.br/labs/kaliope/" data-send="true" data-layout="button_count" data-width="450" data-show-faces="true">
      </div-->
      
      <div id="fb-root"></div>
      <div id="videoDiv"></div>

      <br/>

      <iframe name="iframeVideo" id="iframeVideo" width="640"height="360">
        Nenhum vídeo postado recentemente pelos seus amigos.
      </iframe>
      <div id="kaliope-social"></div>
      <ul id="kaliope-playlist"></ul>
    </body>
 </html>