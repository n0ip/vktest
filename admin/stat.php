<?php

require '../vendor/autoload.php';

$client = new Predis\Client();

$start_date = filter_input( INPUT_GET, 'start_date', FILTER_SANITIZE_STRING);
$end_date = filter_input( INPUT_GET, 'end_date', FILTER_SANITIZE_STRING);
$pid = filter_input( INPUT_GET, 'pid', FILTER_SANITIZE_STRING);

if( $start_date == null ) $start_date = date("Y-m-d");
if( $end_date == null ) $end_date = date("Y-m-d");

$u_clicks = count_clicks( $client, $start_date, $end_date, 'total_unique_clicks_', $pid );
$clicks = count_clicks( $client, $start_date, $end_date, 'click_', $pid );

$json_stats = $json_unique_stats = [];

generate_json($u_clicks, $json_unique_stats);
generate_json($clicks, $json_stats);

$total_project_clicks = $client->hgetall( 'project_clicks_' . $pid . ':' );
$total_project_u_clicks = $client->hgetall( 'project_total_unique_clicks_' . $pid . ':' );

$target_actions = count( $client->hgetall( 'complete_' . $pid ) );

$range = createDateRangeArray($start_date, $end_date);
$targets = [];
foreach ( $range as $date ) {
	$targets['name'] = 'Target actions';
	$targets['data'][] = [ strtotime( $date ) * 1000, count( $client->hgetall( 'complete_' . $pid . $date ) ) ];
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

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>

<!-- Highcharts -->
<script src="http://code.highcharts.com/highcharts.js"></script>
<script src="http://code.highcharts.com/modules/exporting.js"></script>

<!-- Styles -->
<link rel="stylesheet" type="text/css" href="../styles/main.css" />

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">

<!-- Optional theme -->
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css">

<!-- Latest compiled and minified JavaScript -->
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>

<script>
	
var total_clicks = eval('<?=json_encode($json_stats)?>');
var unique_clicks = eval('<?=json_encode($json_unique_stats)?>');
var targets = eval('[<?=json_encode($targets)?>]');

$(function () {
        $('#unique').highcharts({
            chart: {
                type: 'spline'
            },
            title: {
                text: 'Клики'
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
                text: 'Уникальные клики'
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

$(function () {
        $('#targets').highcharts({
            chart: {
                type: 'spline'
            },
            title: {
                text: 'Целевые действия'
            },
            xAxis: {
                type: 'datetime',
                title: {
                    text: 'Дата'
                }
            },
            yAxis: {
                title: {
                    text: 'Целевые действия'
                },
                min: 0
            },
            tooltip: {
                headerFormat: '<b>{series.name}</b><br>',
            },

            series: targets
        });
    });
</script>

</head>

<body>
<noscript><meta http-equiv="refresh" content="0; URL=http://vk.com/badbrowser.php"></noscript>

<h3><a href="admin.php">Назад</a></h3>
<div class="center"><h1> Статистика по проекту <?=$pid?></h1>

<hr />

<form>
	<input type="date" name="start_date" value="<?=$start_date?>">
	<input type="date" name="end_date" value="<?=$end_date?>">
	<input type="hidden" name="pid" value="<?=$pid?>">
	<input type="submit">
</form>

<hr />

<div id="unique" style="min-width: 310px; height: 400px; margin: 0 auto"></div>

<div id="total" style="min-width: 310px; height: 400px; margin: 0 auto"></div>

<div id="targets" style="min-width: 310px; height: 400px; margin: 0 auto"></div>

<div>
<h2>Всего кликов по проекту</h2>
	<table class="table table-striped admin">
	<?php
	foreach($total_project_clicks as $name => $count) {
		echo "<tr><td>".$name."</td><td>".$count."</td></tr>";
	}
	?>
	</table>
</div>

<div>
<h2>Всего уникальных кликов по проекту</h2>
	<table class="table table-striped admin">
	<?php
	foreach($total_project_u_clicks as $name => $count) {
		echo "<tr><td>".$name."</td><td>".$count."</td></tr>";
	}
	?>
	</table>
</div>

<div>
	<h3>Целевых действий за всё время: <?=$target_actions?></h3>
</div>

</div>

</body>
</html>

<?php

function generate_json( $clicks, &$arr ) {
	$i = 0;
	foreach( $clicks as $name => $stat ) {
		$arr[$i]['name'] = $name;
		foreach( $stat as $date => $value ) {
			$arr[$i]['data'][] = [ strtotime( $date ) * 1000, $value ];
		}
		$i++;
	}
}

function count_clicks( $client, $start_date, $end_date, $prefix, $pid ) {

	$result = [];
	$range = createDateRangeArray($start_date, $end_date);

	foreach( $range as $date ) {
		$clicks = $client->hgetall( $prefix . $pid . ':' . $date );
		foreach( $clicks as $action => $count ) {
			if ( empty ($action) ) continue;
			if( !isset( $result[ $action ][ $date ]) ) $result[ $action ][ $date ] = 0;
			$result[ $action ][ $date ] += $count;
		}
	}

	return $result;
}

function createDateRangeArray( $strDateFrom, $strDateTo ) {

    $aryRange=array();

    $iDateFrom=mktime(1,0,0,substr($strDateFrom,5,2), substr($strDateFrom,8,2),substr($strDateFrom,0,4));
    $iDateTo=mktime(1,0,0,substr($strDateTo,5,2), substr($strDateTo,8,2),substr($strDateTo,0,4));

    if ($iDateTo>=$iDateFrom) {
        array_push($aryRange,date('Y-m-d',$iDateFrom)); // first entry
        while ($iDateFrom<$iDateTo) {
            $iDateFrom+=86400; // add 24 hours
            array_push($aryRange,date('Y-m-d',$iDateFrom));
        }
    }
    return $aryRange;
}