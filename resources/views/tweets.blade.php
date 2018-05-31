<!DOCTYPE html>
<html>
<head>
  <title>Tweets Search</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
  <div class="container" style="margin: 0% 25% 50% 50%;">
    <h2>Tweets Search</h2>
      <span class="savetweetsuccess" style="display: none; color: green;"></span>
      <span class="savetweeterror" style="display: none; color: red;"></span>
      <span class="noresult" style="display: none; color: red;"></span>
      <span id="image" style="display: none;"><img src="/images/loading.gif"></span>
  </div>
</body>
  <script src="//code.jquery.com/jquery-1.9.1.js"></script>
  <script src="/js/jquery.js"></script>

  <script type='text/javascript'>
    function getnow() {
      var now = new Date();
      now = `${now.getFullYear()}/${now.getMonth()}/${now.getDate()} ${now.getHours()}:${now.getMinutes()}:${now.getSeconds()}`;
      return now;
    }

    function saveTweet(tweets) {
      $.ajax({
        url: "{{route('post.savetweets')}}",
        type: 'POST',
        dataType: 'json',
        data: {
          'tweets' : JSON.stringify(tweets)
        },
      })
      .done(function(resp) {
        console.log(resp)
        if (resp.status == 'success') {
          $('.savetweetsuccess').text(resp.message);
          $('.savetweetsuccess').css("display", "block");
          $('.noresult').css('display', 'none');
          $('.savetweeterror').css('display', 'none');
        } else {
          if (resp.status == 'error') {
            $('.savetweeterror').text(resp.message);
          } else {
            $('.savetweeterror').text('データー格納失敗しました。');
          }
          $('.savetweeterror').css("display", "block");
          $('.noresult').css('display', 'none');
          $('.savetweetsuccess').css('display', 'none');
        }
      })
      .fail(function(error){
        console.log(error)
      })
    }

    function saveSearchHistory(userName, time, keyword, start, end) {
      $.ajax({
        url: "{{route('post.savesearchinfo')}}",
        type: 'POST',
        dataType: 'json',
        data: {
          'user_name' : userName,
          'searched_at' : time,
          'keyword' : keyword,
          'start' : start,
          'end' : end
        },
      })
      .done(function(resp) {
        console.log(resp)
      })
      .fail(function(error){
        console.log(error)
      })  
    }

    $(function() {
      var now = getnow();
      var url = "{{route('post.gettweets')}}";
      var user_name = "{{$userName}}";
      var keyword = "{{$keyword}}";
      var start_time = "{{$start}}";
      var end_time = "{{$end}}";
      var error_message = '';
      var result = [];

      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });

      $.ajax({
        url: url,
        type: 'POST',
        dataType: 'json',
        data: {
          'keyword': keyword,
          'start_time': start_time,
          'end_time': end_time
        },
        beforeSend: function(){
          $('#image').show();
        },
        complete: function(){
          $('#image').hide();
        },
      })
      .done(function(resp) {
        var error_message = '';
        if (resp.error) {
          if (resp.error.keyword) {
            error_message = error_message + resp.error.keyword + '\n';
          }
          if (resp.error.time) {
            error_message = error_message + resp.error.time;
          }
          if (resp.error.empty_time) {
            error_message = error_message + resp.error.empty_time;
          }
          alert(error_message);
        } else {
          console.log(resp);
          if (typeof resp.tweets.statuses == 'undefined' ||  resp.tweets.statuses.length == 0) {
            $('.noresult').text('検索結果はありません。');
            $('.noresult').css('display', 'block');
            $('.savetweetsuccess').css('display', 'none');
            $('.savetweeterror').css('display', 'none');
          } else {
            saveTweet(resp.tweets.statuses);
          }
          window.open('','_self');
          window.close();
        }
      })
      .fail(function(error) {
        console.log(JSON.parse(error.responseText));
        alert('エラーが発生しました。テックに連絡ください。');
        window.open('','_self').close();
      });


      if (keyword && start_time && end_time) {
        saveSearchHistory(user_name, now, keyword, start_time, end_time);
      }
    });
  </script>
</html>
