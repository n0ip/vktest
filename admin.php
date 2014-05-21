<? 

require 'vendor/autoload.php'; 

$client = new Predis\Client();

$start_date = filter_input( INPUT_GET, 'start_date', FILTER_SANITIZE_STRING);
$end_date = filter_input( INPUT_GET, 'end_date', FILTER_SANITIZE_STRING);

if( $start_date == null ) $start_date = date("Y-m-d");
if( $end_date == null ) $end_date = date("Y-m-d");

$range = createDateRangeArray($start_date, $end_date);

$pid = 1;

$unique_stats = [];
$stats = [];

foreach( $range as $date ) {
	
	$data = $client->hgetall( 'ucl_' . $pid . ':' . $date );
	
	foreach( $data as $key => $stat ) {
		$elem = [];
		if( !empty( $key ) ) {
			list( $elem['uid'], $elem['action'] ) = explode(":", $key);

			if( !isset($unique_stats[ $elem['action'] ][$date]) ) $unique_stats[ $elem['action'] ][$date] = 0;
			if( !isset( $stats[$date][ $elem['action'] ]) ) $stats[ $elem['action'] ][ $date ] = 0;
			
			$unique_stats[ $elem['action'] ][ $date ] += 1;
			$stats[ $elem['action'] ][ $date ] += $stat;
		}
	}

}

$json_stats = [];
$json_unique_stats = [];

$i = 0;

foreach( $stats as $name => $stat ) {
	$json_stats[$i]['name'] = $name;
	foreach( $stat as $date => $value ) {
		$json_stats[$i]['data'][] = [ strtotime( $date ) * 1000, $value ];
	}
	$i++;
}

$i = 0;

foreach( $unique_stats as $name => $stat ) {
	$json_unique_stats[$i]['name'] = $name;
	foreach( $stat as $date => $value ) {
		$json_unique_stats[$i]['data'][] = [ strtotime( $date ) * 1000, $value ];
	}
	$i++;
}

function createDateRangeArray( $strDateFrom, $strDateTo )
{
	
    $aryRange=array();

    $iDateFrom=mktime(1,0,0,substr($strDateFrom,5,2), substr($strDateFrom,8,2),substr($strDateFrom,0,4));
    $iDateTo=mktime(1,0,0,substr($strDateTo,5,2), substr($strDateTo,8,2),substr($strDateTo,0,4));

    if ($iDateTo>=$iDateFrom)
    {
        array_push($aryRange,date('Y-m-d',$iDateFrom)); // first entry
        while ($iDateFrom<$iDateTo)
        {
            $iDateFrom+=86400; // add 24 hours
            array_push($aryRange,date('Y-m-d',$iDateFrom));
        }
    }
    return $aryRange;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<link rel="shortcut icon" href="http://vk.com/images/faviconnew.ico?3" />

<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta name="description" content="" />

<title>VKStats</title>

<!-- AngularJS libs -->
<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.2.16/angular.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.2.16/angular-resource.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.2.16/angular-route.js"></script>

<!-- Jquery -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>

<!-- Client-side JS library -->
<script src="VKStatsApp.js"></script>

<!-- Highcharts -->
<script src="http://code.highcharts.com/highcharts.js"></script>
<script src="http://code.highcharts.com/modules/exporting.js"></script>

<!-- Styles -->
<link rel="stylesheet" type="text/css" href="main.css" />

<script>
	
var total_clicks = eval('<?=json_encode($json_stats)?>');
var unique_clicks = eval('<?=json_encode($json_unique_stats)?>');
	
$(function () {
        $('#unique').highcharts({
            chart: {
                type: 'spline'
            },
            title: {
                text: 'Суммарно кликов'
            },
            xAxis: {
                type: 'datetime',
                title: {
                    text: 'Дата'
                }
            },
            yAxis: {
                title: {
                    text: 'Количество кликов'
                },
                min: 0
            },
            tooltip: {
                headerFormat: '<b>{series.name}</b><br>',
            },

            series: total_clicks
        });
    });
	
$(function () {
        $('#total').highcharts({
            chart: {
                type: 'spline'
            },
            title: {
                text: 'Статистика по уникальным кликам'
            },
            xAxis: {
                type: 'datetime',
                title: {
                    text: 'Дата'
                }
            },
            yAxis: {
                title: {
                    text: 'Количество кликов'
                },
                min: 0
            },
            tooltip: {
                headerFormat: '<b>{series.name}</b><br>',
            },

            series: unique_clicks
        });
    });	
</script>

</head>

<body>
<noscript><meta http-equiv="refresh" content="0; URL=http://vk.com/badbrowser.php"></noscript>

<form>
	<input type="date" name="start_date" value="<?=$start_date?>">
	<input type="date" name="end_date" value="<?=$end_date?>">
	<input type="submit">
</form>
<div id="unique" style="min-width: 310px; height: 400px; margin: 0 auto"></div>

<div id="total" style="min-width: 310px; height: 400px; margin: 0 auto"></div>

</body>
</html>
