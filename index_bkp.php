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
        
        /* TODO: alinhar decentemente sem table */
        .kaliope-container {width:100%;}        
        .kaliope-left   {width:20%;}
        .kaliope-center {width:60%;}
        .kaliope-right  {width:20%;}
        
        body {
          font-family: Tahoma;
          font-size: 10pt;
          background-color: #111122;
        }
        
        .kaliope-playlist{
          background-color : #888888;
          border-radius: 5px;
          display: table-cell;
        }
        
        .kaliope-player{
          text-align: center;
           background-color : #888888;
           border-radius: 15px;
        }        


        .kaliope-playlist ul {
          list-style: none;
        }
        
        .kaliope-playlist a {
          color: #ffffff;
        
        }
        
        .kaliope-playlist li:hover{
          background-color: #8888ff;
        }
        
        .kaliope-social a {
          color: #8888ff;
        }
         
        a{
          text-decoration: none;
        }
        .kaliope-social{
          color: #dddddd;
        }
        
        
        .kaliope-iframe{
          border-style: none;
          width: 640px;
          height: 360px;
        }
        
        .kaliope-container{
          
        }
        .kaliope-large-button{
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
           var newest, oldest;
           
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
               FB.api('/me/permissions', function (response) {
                 if(response.data[0].read_stream==1){
                   start();               
                 } else {
                   login();
                 }
               });
             } else {
               login();
             }
             FB.Event.subscribe('auth.authResponseChange', startIfConnected);
           };
           
           var login = function(){
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
             $('#iframeVideo').attr('src', p.attachment.media[0].video.source_url);
             updateSocial(p);
             playerLoaded  = true;
           };
           
           var fqlQuery = function(query, callback){
              $.getJSON('https://graph.facebook.com/fql?q='+query
                       +'&access_token='+getToken(), callback);
           }
                  
           var updateSocial = function(p){
           
             //var query='';
             //query += ' SELECT name ';
             //query += ' FROM user WHERE uid =' + p.actor_id + ';';

              $.getJSON('https://graph.facebook.com/'+p.actor_id
                       +'&access_token='+getToken(), function(response){
        
               var info = '';
               info += 'Postado por <strong>';
               info += '<a href=\'https://www.facebook.com/'+p.actor_id+'\' target=\'_blank\'>';
               info += response.name;
               info += '</strong></a>';
               info += ' ';
               info += '<a href=\''+p.permalink+'\' target=\'_blank\'>';
               info += (new Date(p.created_time*1000)).toString();
               info += '</a>';               
               $('.kaliope-social').html(info);
               
             });
             
           }
                  
           var showAllPosts = function(){
             for(i in postList){
               var p = posts[i];
               appendPost(p);
             }
           };

          var appendPost = function(p){
             var a = $('<a>');
             var list = $('.kaliope-playlist ul');
             a.attr('href', p.attachment.media[0].video.source_url);
             a.attr('title', p.attachment.description);
             //a.attr('target', '_blank');
             a.attr('target', 'iframeVideo');
             a.click(function(){updateSocial(p)});
             a.append(p.attachment.name);
             list.append($('<li/>').append(a));
           };
         
           var loadFeed = function(since, until){
              //FB.api('/me/home?limit='+POSTLIMIT +'&offset='+paging*POSTLIMIT, function(response) {
              
              var query='';
              
              query += ' SELECT post_id, created_time, actor_id, attachment, permalink ';
              query += ' FROM stream WHERE filter_key in ';
              query += ' (SELECT filter_key FROM stream_filter WHERE uid=me() AND type=\'newsfeed\') ';

              if(since && until){
                query += ' AND ( created_time > ' + since + ' ';
                query += (since < until)?'AND':'OR';
                query += ' created_time < ' + until + ' ) ';                
              } else {
                if(since){ query += ' AND created_time > ' + since + ' ';}
                if(until){ query += ' AND created_time < ' + until + ' ';}
              }
              
              query += ' ORDER BY created_time DESC LIMIT 50;'
              
              //console.log(query);

              fqlQuery(query, function(response){

                 var posts = response.data;
                 var p, id, s;

                 //console.log(response);

                 for(id in posts){
                   p = posts[id];
                   if(s=p.attachment){
                     if(s=s.media){
                       if(s=s[0]){
                         if(s=s.video){
                           s=s.source_url;
                         }
                       }
                     }
                   }           
                           
                   if(s){// && s.search(/youtube/)>0){
                     //if(!postList[p.post_id]){}//TODO:use video id
                     postList[p.post_id] = p;         
                     appendPost(p);           
                     if(!playerLoaded){loadPlayer(p);}
                   }
                   if(newest<p.created_time||!newest){newest=p.created_time;}
                   if(oldest>p.created_time||!oldest){oldest=p.created_time;}
                 }

                 if(posts.length){
                   setTimeout(function(){
                     loadFeed(null,oldest);
                   }, 1000);
                 } else {
                   setTimeout(function(){
                     loadFeed(newest,null);
                   }, 10000);
                 }
             });
           }
         }         
      </script>

      <div id="fb-root"></div>

      <div class="kaliope">
        <table class="kaliope-container">
          <tr>
            <td class="kaliope-left">&nbsp;</td>
            <td class="kaliope-center">
	      <div class="kaliope-player">
                <span class="kaliope-large-button">&nbsp;</span>
                <iframe name="iframeVideo" id="iframeVideo" class="kaliope-iframe">
                </iframe>
                <span class="kaliope-large-button">&nbsp;</span>
              </div>
              <div class="kaliope-social"></div>	
              <div class="kaliope-playlist">
                <ul></ul>
              </div>
            </td>
            <td class="kaliope-playlist-right">&nbsp;</td>
          </tr>
        </table>
      </div>

    </body>
 </html>