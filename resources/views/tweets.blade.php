<!DOCTYPE html>
<html>
<head>
  <title>Tweets Search</title>
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
      now = `${now.getFullYear()}/${now.getMonth()+1}/${now.getDate()} ${now.getHours()}:${now.getMinutes()}:${now.getSeconds()}`;
      return now;
    }

    function saveSearchHistory(userName, time, keyword, start, end, st) {
      $.ajax({
        url: "{{route('get.savesearchinfo')}}",
        type: 'GET',
        dataType: 'json',
        data: {
          'user_name' : userName,
          'searched_at' : time,
          'keyword' : keyword,
          'start' : start,
          'end' : end,
          'status' : st
        },
      })
      .done(function(resp) {
        console.log(resp)
      })
      .fail(function(error){
        console.log(error)
      })  
    }

    function getstatus() {
      var status = '';
      if ($('.savetweetsuccess').text()) {
        status += $('.savetweetsuccess').text();
      } else if ($('.savetweeterror').text()) {
        status += $('.savetweeterror').text();
      } else if ($('.noresult').text()) {
        status += $('.noresult').text();
      }

      return status;
    }

    $(function() {
      var now = getnow();
      var url = "{{route('get.gettweets')}}";
      var user_name = "{{$userName}}";
      var keyword = "{{$keyword}}";
      var start_time = "{{$start}}";
      var end_time = "{{$end}}";
      var error_message = '';
      var result = [];
      var st = '';

      $.ajax({
        url: url,
        type: 'GET',
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
          console.log(resp.error);
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
          if (typeof resp.status != 'undefined' && resp.status == 'error') {
            $('.savetweetsuccess').text('');
            $('.savetweetsuccess').css('display', 'none');
            $('.savetweeterror').text(resp.message);
            $('.savetweeterror').css('display', 'block');
          } else if (typeof resp.status != 'undefined' && resp.status == 'success') {
            $('.savetweetsuccess').text(resp.message);
            $('.savetweetsuccess').css('display', 'block');
            $('.savetweeterror').text('');
            $('.savetweeterror').css('display', 'none');
          }
          saveSearchHistory(user_name, now, keyword, start_time, end_time, getstatus())
          window.open('','_self').close();
        }
      })
      .fail(function(error) {
        $('.savetweetsuccess').text('');
        $('.savetweetsuccess').css('display', 'none');
        $('.savetweeterror').text('エラーが発生しました。テックに連絡ください。');
        $('.savetweeterror').css('display', 'block');
        if (keyword && start_time && end_time) {
          saveSearchHistory(user_name, now, keyword, start_time, end_time, getstatus());
        }
        window.open('','_self').close();
      });
    });
  </script>
</html>
