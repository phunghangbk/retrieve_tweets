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
        <input type="text" name="keyword" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Start Time:</label>
        <input type="text" name="start_time" class="form-control" id="startTimeDateTimePicker">
      </div>
      <div class="form-group">
        <label>End Time:</label>
        <input type="text" name="end_time" class="form-control" id="endTimeDateTimePicker">
      </div>
      <span class="help-block" style="display: none;color: red;" id="warnning"></span>
      <div class="form-group">
        <button id="submit" class="btn btn-success">Search</button>
      </div>
  </div>
</body>
  <link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.css"/>
  <script src="//code.jquery.com/jquery-1.9.1.js"></script>
  <script src="/js/jquery.js"></script>
  <script src="/js/jquery.datetimepicker.full.min.js"></script>

  <script type="text/javascript">
    var oneWeekAgo = new Date();
    $.datetimepicker.setLocale('ja');
    oneWeekAgo.setDate(oneWeekAgo.getDate() - 7);
    $('#startTimeDateTimePicker').datetimepicker({ minDate: oneWeekAgo });
    $('#endTimeDateTimePicker').datetimepicker({ minDate: oneWeekAgo });
  </script>

  <script type='text/javascript'>
    $("#submit").click(function(event) {
      var url = "{{route('post.gettweets')}}";
      var user_name = "{{$userName}}";
      var keyword = $('input[name="keyword"]').val();
      var start_time = $('input[name="start_time"]').val();
      var end_time = $('input[name="end_time"]').val();
      var error_message = '';
      var warn_message = '';
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
          'end_time': end_time,
          'user_name': user_name
        },
      })
      .done(function(resp) {
        if (resp.errors) {
          if (resp.errors.keyword) {
            error_message = error_message + resp.errors.keyword + '\n';
          }
          if (resp.errors.time) {
            error_message = error_message + resp.errors.time;
          }
          alert(error_message);
        }

        if (resp.warns && resp.warns.length > 0) {
          warn_message = warn_message + resp.warns.time;
          console.log($('#warnning'));
          $('#warnning').text(warn_message);
          $('#warnning').css('display', 'block');
        }
        console.log(resp);
      })
      .fail(function(error) {
        alert(JSON.parse(error.responseText).keyword);
      });
    });
  </script>
</html>