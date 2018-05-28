<!DOCTYPE html>
<html>
<head>
  <title>Twitter API</title>
  <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
  <div class="container">
    <h2>Twitter Search API</h2>
      <div class="form-group">
        <label>Keyword:</label>
        <input type="text" name="keyword" class="form-control keyword" required>
      </div>
      <div class="form-group">
        <label>Start Time:</label>
        <input type="text" name="start_time" class="form-control start_time" id="startTimeDateTimePicker">
      </div>
      <div class="form-group">
        <label>End Time:</label>
        <input type="text" name="end_time" class="form-control end_time" id="endTimeDateTimePicker">
      </div>
      <span class="help-block" style="display: none;color: red;" id="warnning"></span>
      <div class="form-group">
        <button id="submit" class="btn btn-success">Search</button>
      </div>
      <span class="savetweetsuccess" style="display: none; color: green;"></span>
      <span class="savetweeterror" style="display: none; color: red;"></span>
      <span class="noresult" style="display: none; color: red;"></span>
      <span id="image" style="display: none;"><img src="/images/loading.gif"></span>
  </div>
</body>
  <link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.css"/>
  <script src="//code.jquery.com/jquery-1.9.1.js"></script>
  <script src="/js/jquery.js"></script>
  <script src="/js/jquery.datetimepicker.full.min.js"></script>

  <script type="text/javascript">
    var oneWeekAgo = new Date();
    $.datetimepicker.setLocale('ja');
    $('#startTimeDateTimePicker').datetimepicker();
    $('#endTimeDateTimePicker').datetimepicker();
  </script>

  <script type='text/javascript'>
    $(function() {
      $('.keyword').val("{{$keyword}}")
      $('.start_time').val("{{$start}}")
      $('.end_time').val("{{$end}}")
    });
    
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
        } else {
          if (resp.status == 'error') {
            $('.savetweeterror').text(resp.message);
          } else {
            $('.savetweeterror').text('データー格納失敗しました。');
          }
          $('.savetweeterror').css("display", "block");
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

    $("#submit").click(async function(event) {
      var now = getnow();
      var url = "{{route('post.gettweets')}}";
      var user_name = "{{$userName}}";
      var keyword = $('input[name="keyword"]').val();
      var start_time = $('input[name="start_time"]').val();
      var end_time = $('input[name="end_time"]').val();
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
          } else {
            saveTweet(resp.tweets.statuses);
          }
        }
      })
      .fail(function(error) {
        console.log(JSON.parse(error.responseText));
        alert('エラーが発生しました。テックに連絡ください。');
      });

      if (keyword && start_time && end_time) {
        saveSearchHistory(user_name, now, keyword, start_time, end_time);
      }
    });
  </script>
</html>
