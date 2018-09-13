<?php

include(PCHART_DIR."class/pData.class.php"); 
include(PCHART_DIR."class/pDraw.class.php"); 
include(PCHART_DIR."class/pImage.class.php"); 
include(PCHART_DIR."class/pPie.class.php"); 

/**
 * @brief calculate coordinates based on scaled input
 * scales the input xywh to the actual and then adds together
 * to give new absolute coordinates
 * @param $x1 up left corner
 * @param $y1 up left corner
 * @param $x2 bottom right corner
 * @param $y2 bottom right corner
 */
function pchart_draw_calc_xy(&$x1, &$y1, &$x2, &$y2, $x, $y, $w, $h)
{
    #determine scale and then scale all inputs
    $aw = $x2 - $x1;
    $ah = $y2 - $y1;

    $x1 += $aw * $x;
    $y1 += $ah * $y;

    $x2 = $x1 + ($aw * $w);
    $y2 = $y1 + ($ah * $h);
}

/**
 * @brief get list of series in pchart data
 */
function pchart_get_all_series(&$data)
{
    $series = array();
    {
	$pd = &$data->getdata();

	if(isset($pd['Series']))
	    $series = array_keys($pd['Series']);
    }
    return $series;
}

/**
 * @brief only show the following series
 * @param $regex use a regex on list
 * @param $required all series must be set to visible, if any are invalid: die
 */
function pchart_only_series(&$data, $series, $regex = FALSE, $required = FALSE)
{
    $all = pchart_get_all_series($data);
    $data->setSerieDrawable($all, false);
    if($regex)
	$series = pchart_filter_series($data, $series);
    $visible = array_intersect($all, $series);
    #var_dump(array($series, $visible));
    if($required && count($visible) != count($series))
    {
	echo "All Possible:\n";    
	var_dump($all);
	echo "Requested:\n";    
	var_dump($series);
	echo "Visible:\n";    
	var_dump($visible);
	die("Series not found.");
    }
    $data->setSerieDrawable($visible, true); 
}

/**
 * @brief get filtered list of series in pchart data
 */
function pchart_filter_series(&$data, $filters)
{
    $fs = array();
    $series = pchart_get_all_series($data, $filters);

    if(count($series) > 0)
	foreach($filters as $filter)
	{
	    $ls = preg_grep('/'.$filter.'/', $series);
	    if($ls !== FALSE)
		foreach($ls as $v)
		    $fs[] = $v;
	}

    return array_unique($fs);
}

/**
 * @brief set pchart series properties
 * this is called as a child of graphing elements
 */
function pchart_set_series(&$myPicture, &$data, &$text, &$SETUP, &$list) 
{
    foreach($list as $sr => $prop)
    {
	if(isset($prop['name'])) 
	    $data->setSerieDescription($sr,$prop['name']);
	if(isset($prop['palette'])) 
	    $data->setPalette($sr, $prop['palette']);
    }
}

/**
 * @brief recursivly draw elements from template
 * takes template input and draws each element while scaling the 
 * the coordinates to have everything fit as best as possible
 * @param $myPicture pchart picture
 * @param $data pchart data
 * @param $text Text array to draw (pchart data doesnt hold generic text)
 * @param $SETUP chart setup
 * @param $ax1 absolute up left corner
 * @param $ay1 absolute up left corner
 * @param $ax2 absolute bottom right corner
 * @param $ay2 absolute bottom right corner 
 * @param $parent parent element to draw
 */
function pchart_draw_element(&$myPicture, &$data, &$text, &$SETUP, $ax1, $ay1, $ax2, $ay2, &$parent)
{
    #var_dump($parent);die;
    foreach($parent as $element => $values)
    {
        $x1 = $ax1;
        $y1 = $ay1;
        $x2 = $ax2;
        $y2 = $ay2;

	#must be an array or it is a property of parent
	if(is_numeric($element) && is_array($values))
	switch($values['type'])
	{
	    case 'div':
		pchart_draw_calc_xy($x1, $y1, $x2, $y2, $values['x'], $values['y'], $values['w'], $values['h']);
		pchart_draw_element($myPicture, $data, $text, $SETUP, $x1, $y1, $x2, $y2, $values['draw']);
		break;
	    case 'shadow':
		if(isset($values['format']))
		    $myPicture->setShadow($values['active'], $values['format']);
		else
		    $myPicture->setShadow($values['active']);
		break;
	    case '3dpie':
		pchart_draw_calc_xy($x1, $y1, $x2, $y2, $values['x'], $values['y'], 1, 1);

		$data->setAbscissa($values['abscissa']);

                if(isset($values['series']))
		    pchart_set_series($myPicture, $data, $text, $SETUP, $values['series']); 

		pchart_only_series($data, array($values['values'], $values['abscissa']));
		#$data->setSerieDrawable(pchart_get_all_series($data), false);

		#var_dump($data->getData());die;
		$format = $values['format'];
		#set the radius using the scaled radius
		$format['Radius'] = min($ax2 - $ax1, $ay2 - $ay1) * $values['r'];

		$PieChart = new pPie($myPicture, $data);

                if(isset($values['palette']))
		    foreach($values['palette'] as $slice => $palette)
			$PieChart->setSliceColor($slice, $palette);
 
		$PieChart->draw3DPie($x1,$y1, $format);

		#legend does spacing based on parent, not the frame of the pie
		if(isset($values['legend']))
		{
		    $x1 = $ax1;
		    $y1 = $ay1;
		    $x2 = $ax2;
		    $y2 = $ay2;

		    pchart_draw_calc_xy($x1, $y1, $x2, $y2, $values['legend']['x'], $values['legend']['y'], 1, 1);
		    $PieChart->drawPieLegend($x1, $y1, $values['legend']['format']);
		}
 
		break;
 	    case 'graph':
		pchart_draw_calc_xy($x1, $y1, $x2, $y2, $values['x'], $values['y'], $values['w'], $values['h']);
		
		$data->setAbscissa($values['abscissa']);

		foreach($values['axis'] as $axis => $prop)
		{
		    if(isset($prop['units'])) 
		        $data->setAxisUnit($axis,$prop['units']);
		    if(isset($prop['name'])) 
			$data->setAxisName($axis,$prop['name']); 
		}

		if(isset($values['series']))
		    pchart_set_series($myPicture, $data, $text, $SETUP, $values['series']);
		
		#var_dump($values['scale']['series']);die;
		if(isset($values['scale']['series']))
		    pchart_only_series($data, $values['scale']['series'], true);
		else #series must be visible or scale may go insane
		    $data->setSerieDrawable(pchart_get_all_series($data), true);

		$myPicture->setGraphArea($x1,$y1,$x2,$y2);
		#var_dump(array($x1,$y1,$x2,$y2));
		$myPicture->drawFilledRectangle($x1,$y1,$x2,$y2, $values['background']);

		if($values['scale']['Mode'] == SCALE_MODE_MANUAL)
		{
		    $pd = $data->getdata();

		    if(isset($values['scale']['YMax']))
			$values['scale']['ManualScale'][0]['Max'] = $pd['Series'][$values['scale']['YMax']]['Data'][0];
 		    if(isset($values['scale']['YMin']))
			$values['scale']['ManualScale'][0]['Min'] = $pd['Series'][$values['scale']['YMin']]['Data'][0];
  		    if(isset($values['scale']['XMin']))
			$values['scale']['ManualScale'][1]['Min'] = $pd['Series'][$values['scale']['XMin']]['Data'][0];
   		    if(isset($values['scale']['XMax']))
			$values['scale']['ManualScale'][1]['Max'] = $pd['Series'][$values['scale']['Xmax']]['Data'][0];
		    #var_dump($values['scale']);die;
		}
		$myPicture->drawScale($values['scale']);

		#draw the graph contents
		pchart_draw_element($myPicture, $data, $text, $SETUP, $x1, $y1, $x2, $y2, $values['draw']);
		break; 
 	    case 'stack':
		pchart_only_series($data, $values['series'], true);
		$myPicture->drawStackedAreaChart($values['format']);
		break; 
  	    case 'bar':
		pchart_only_series($data, $values['series'], true);
		$myPicture->drawBarChart($values['format']);
		break; 
	    case 'zone':
		$myPicture->drawZoneChart($values['high'], $values['low'], $values['format']);
		break;
 	    case 'line':
		pchart_only_series($data, $values['series'], true);
		$myPicture->drawLineChart();
		break; 
	   case 'legend':
		pchart_draw_calc_xy($x1, $y1, $x2, $y2, $values['x'], $values['y'], 1, 1);
		pchart_only_series($data, $values['series'], true);
		$myPicture->drawLegend($x1, $y1, $values['format']);
		break;
           case 'font':
		$myPicture->setFontProperties($values['format']);
		break; 
 	   case 'rectangle':
		pchart_draw_calc_xy($x1, $y1, $x2, $y2, $values['x'], $values['y'], $values['w'], $values['h']);
		$myPicture->drawRectangle($x1, $y1, $x2, $y2, $values['format']); 
		break;  
  	   case 'text':
		pchart_draw_calc_xy($x1, $y1, $x2, $y2, $values['x'], $values['y'], $values['w'], $values['h']);

		$txt = FALSE;
		if(isset($values['content']))
		    $txt = $text[$values['content']];
                if(isset($values['value']))
		    $txt = $values['value']; 

		if(isset($values['require']))
		{
		    if($data->getSerieCount($values['require']) == 0)
			$txt = FALSE;
		}

		if($txt !== FALSE)
		{
		    #determine the bounding box of the text and make sure it wont over flow the 
		    #given size of the div, if it does: walk the size down until it doesnt
		    $angle = isset($values['format']['Angle']) ? $values['format']['Angle'] : 0;
     
		    $osize = $myPicture->FontSize; #orginal size to restore values later
		    $size = $myPicture->FontSize;
		    $w = $x2 - $x1;
		    $h = $y2 - $y1;
		    do
		    {
			$box = $myPicture->getTextBox($x1, $y1, $myPicture->FontName,$size, $angle, $txt);
			$txtw = $box[1]["X"] - $box[0]["X"];
			$txth = $box[1]["Y"] - $box[0]["Y"];
			$over = false;
			if($txtw > $w || $txth > $h)
			{
			    $over = true;
			    --$size;
			}
		    } while($over);
		    $myPicture->FontSize = $size;
		    $myPicture->drawText($x1, $y1, $txt, $values['format']);   
		    $myPicture->FontSize = $osize;
		}
		break;   
           case 'png':
		pchart_draw_calc_xy($x1, $y1, $x2, $y2, $values['x'], $values['y'], 1, 1);
		$myPicture->drawFromPNG($x1, $y1, $values['src']);
		break;   
	}
    }
}

/**
 * @brief Splice report into a theme template
 */
function pchart_splice_report(&$theme, &$report)
{
    foreach($theme as $element => &$values)
    {
	if(is_array($values))
	{
	    if(isset($values['type']) && $values['type'] == 'report')
	    {
		$theme[$element] = $report;
		return TRUE;
	    } 

	    if(pchart_splice_report($values, $report))
		return TRUE;
	}
    }

    return FALSE;
}

/**
 * @brief Draw Chart from Template
 * @param $myPicture pchart picture object
 * @param $data pchart data object
 * @param $text Text array to draw (pchart data doesnt hold generic text)
 * @param $tpl format template
 * @param $SETUP graph setup
 */
function pchart_draw(&$data, &$text, &$tpl, $SETUP)
{
    $aw = $SETUP['chart']['width']; #absolute width 
    $ah = $SETUP['chart']['height']; #absolute height 
    $x1 = 0; #box up left
    $y1 = 0; #box up left 
    $x2 = $aw; #box bottom right
    $y2 = $ah; #box bottom right 

    $theme = $GLOBALS['THEMES'][$SETUP['chart']['theme']['name']];

    $myPicture = new pImage($aw,$ah, $data); 
    $myPicture->AntiAlias = TRUE;

    #splice the report into the theme
    pchart_splice_report($theme, $tpl);
    #var_dump($theme);die;
    #draw everything
    pchart_draw_element($myPicture, $data, $text, $SETUP, $x1, $y1, $x2, $y2, $theme);

    #var_dump($theme);die;
    $myPicture->stroke();
}

?>
