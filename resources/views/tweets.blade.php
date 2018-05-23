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
      <span class="savetweetsuccess" style="display: none; color: green;"></span>
      <span class="savetweeterror" style="display: none; color: red;"></span>
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
      var mon = dateObject.getMonth() + 1
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

    function getDateTimes(start, end, dates) {
      var dateTimes = [];
      var length = dates.length;
      if (length == 0) {
        dateTimes.push({'start_time': '', 'end_time': ''});
        return dateTimes;
      }
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

        dateTimes.push({'start_time': start_time, 'end_time': end_time});
      }
      return dateTimes;
    }

    async function doAjax(url, keyword, start_time, end_time) {
      var result;
      try {
        result = await $.ajax({
          url: url,
          type: 'POST',
          dataType: 'json',
          data: {
            'keyword': keyword,
            'start_time': start_time,
            'end_time': end_time
          },
        })
        if (result.tweets.statuses.length > 0) {
          result.tweets.statuses = (sort(result.tweets.statuses)).splice(0, 30);
        }
        return result;
      } catch (e) {
        return e;
      }
    }

    function asyncAjax(url, keyword, array, callback) {
      var promises = [];
      for (var i = 0; i < array.length; i++) {
        promises.push(callback(url, keyword, array[i].start_time, array[i].end_time));
      }

      return promises;
    }

    function sort(tweets) {
      tweets.sort(function(a, b) {
        return (a.retweet_count > b.retweet_count) ? -1 : 1;
      });
      return tweets;
    }

    async function saveTweet(tweets) {
      var result;
      try {
        result = await $.ajax({
          url: "{{route('post.savetweets')}}",
          type: 'POST',
          dataType: 'json',
          data: {
            'tweets' : tweets
          },
        })
        return result;
      } catch (e) {
        return e;
      }
    }

    $("#submit").click(async function(event) {
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
      var dateTimes = getDateTimes(start, end, dates);
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });

      var result = await Promise.all(asyncAjax(url, keyword, dateTimes, doAjax))
      var statuses = [];
      result.forEach( function(element, index) {
        if (element.error) {
          var error_message = '';
          if (element.error.keyword) {
            error_message += element.error.keyword + '\n';
          }
          if (element.error.empty_time) {
            error_message += element.error.empty_time + '\n';
          }

          if (! element.error.keyword && ! element.error.empty_time) {
            error_message = 'エラーが発生しました。再度検索してみてください。';
          }
          alert(error_message);
        } else {
          element.tweets.statuses.forEach( function(el, i) {
            statuses.push(el);
          });
        }
      });

      statuses = sort(statuses);
      if (statuses.length > 30) {
        statuses = statuses.splice(0, 30);
      }
      console.log(statuses);
      saveTweet(statuses).then(function(result) {
        console.log(result);
        if (result.status == 'success') {
          $('.savetweetsuccess').text(result.message);
          $('.savetweetsuccess').css("display", "block");
        } else {
          if (result.status == 'error') {
            $('.savetweeterror').text(result.message);
          } else {
            $('.savetweeterror').text('データー格納失敗しました。');
          }
          $('.savetweeterror').css("display", "block");
        }
      });
    });
  </script>
</html>
