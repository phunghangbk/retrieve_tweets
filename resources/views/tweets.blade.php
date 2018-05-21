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
    $('#startTimeDateTimePicker').datetimepicker();
    $('#endTimeDateTimePicker').datetimepicker();
  </script>

  <script type='text/javascript'>
    var getDates = function(startDate, endDate) {
      var dates = [],
      currentDate = startDate,
      addDays = function(days) {
        var date = new Date(this.valueOf());
        date.setDate(date.getDate() + days);
        return date;
      };
      while (currentDate <= endDate) {
        dates.push(currentDate);
        currentDate = addDays.call(currentDate, 1);
      }
      return dates;
    };

    function convertDateToString(dateObject) {
      var date = dateObject.getDate()
      date = date > 9 ? date : '0' + date
      var mon = dateObject.getMonth()
      mon = mon > 9 ? mon : '0' + mon
      var year = dateObject.getFullYear()

      return `${year}/${mon}/${date}`;
    }

    function convertTimeToString(date) {
      var hour = date.getHours()
      hour = hour > 9 ? hour : '0' + hour
      var min = date.getMinutes()
      min = min > 9 ? min : '0' + min
      var sec = date.getSeconds()
      sec = sec > 9 ? sec : '0' + sec

      return `${hour}:${min}:${sec}`;
    }

    $("#submit").click(function(event) {
      var url = "{{route('post.gettweets')}}";
      var user_name = "{{$userName}}";
      var keyword = $('input[name="keyword"]').val();
      var start = new Date($('input[name="start_time"]').val());
      var end = new Date($('input[name="end_time"]').val());
      var error_message = '';
      var warn_message = '';

      var dates = getDates(start, end);
      var start_time = '';
      var end_time = '';
      console.log(start);
      var length = dates.length;
      for (var i = 0; i < length; i++) {
        if (length == 1) {
          start_time = `${convertDateToString(dates[i])} ${convertTimeToString(start)}`;
          end_time = `${convertDateToString(dates[i])} ${convertTimeToString(end)}`;
        } else if (i == 0) {
          start_time = `${convertDateToString(dates[i])} ${convertTimeToString(start)}`;
          end_time = `${convertDateToString(dates[i])} 23:59:59`;
        } else if (i == length - 1) {
          start_time = `${convertDateToString(dates[i])} 00:00:00`;
          end_time = `${convertDateToString(dates[i])} ${convertTimeToString(end)}`;
        } else {
          start_time = `${convertDateToString(dates[i])} 00:00:00`;
          end_time = `${convertDateToString(dates[i])} 23:59:59`;
        }
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
          console.log(resp);
        })
        .fail(function(error) {
          alert(JSON.parse(error.responseText).keyword);
        });
      }
    });
  </script>
</html>