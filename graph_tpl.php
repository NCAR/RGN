<?php

$THEMES['simple'] = array(
    array(
	'type' => 'font',
	'format' => array(
	    "FontName"=>'Forgotte',
	    #"FontName"=>PCHART_DIR."/fonts/Forgotte.ttf",
	    "FontSize"=>14,
	    "R"=>80,
	    "G"=>80,
	    "B"=>80
	),
    ), 
#    array(
#	'type' => 'rectangle',
#	'x' => 0,
#	'y' => 0,
#	'w' => 0.99,
#	'h' => 0.99,
#	'format' => array(
#	    'R'=>0,
#	    "G"=>0,
#	    "B"=>0
#	)
#    ),
    array(
	'type' => 'shadow',
	'active' => FALSE,
    ),
    array(
	'type' => 'div',
	'x' => 0,
	'y' => 0.1,
	'w' => 1,
	'h' => 0.9, 
	'draw' => array(
	    'type' => 'report'
	)
    ),
    array(
	'type' => 'font',
	'format' => array(
	    "FontName"=>'calibri',
	    #"FontName"=>PCHART_DIR."fonts/calibri.ttf",
	    "FontSize"=>40
	)
    ),
    array(
	'type' => 'text',
	'content' => 'title',
	'x' => 0.5,
	'y' => 0.005, 
	'w' => 0.99,
	'h' => 0.1, 	
	'format' => array(
	    "R"=>0,
	    "G"=>0,
	    "B"=>0,
	    "Align"=>TEXT_ALIGN_TOPMIDDLE
	)
    ),
    array(
	'type' => 'text',
	'value' => 'SSG',
	'x' => 0.98,
	'y' => 0.98,
	'w' => 0.3,
	'h' => 0.3, 	
	'format' => array(
	    "R"=>0,
	    "G"=>0,
	    "B"=>0,
	    "Align"=>TEXT_ALIGN_BOTTOMRIGHT,
	    "Alpha"=>10
	)
    ) 
);       

$THEMES['cisl'] = array(
    array(
	'type' => 'font',
	'format' => array(
	    "FontName"=>'Forgotte',
	    #"FontName"=>PCHART_DIR."/fonts/Forgotte.ttf",
	    "FontSize"=>14,
	    "R"=>80,
	    "G"=>80,
	    "B"=>80
	),
    ), 
    array(
	'type' => 'rectangle',
	'x' => 0,
	'y' => 0,
	'w' => 0.99,
	'h' => 0.99,
	'format' => array(
	    'R'=>0,
	    "G"=>0,
	    "B"=>0
	)
    ),
    array(
	'type' => 'png',
	'x' => 0,
	'y' => 0,
	'src' => 'img/cisl.png'
    ),
    array(
	'type' => 'shadow',
	'active' => TRUE,
	'format' => array(
	    "X"=>5,
	    "Y"=>2,
	    "R"=>0,
	    "G"=>0,
	    "B"=>0,
	    "Alpha"=>10
	)
    ),
    array(
	'type' => 'div',
	'x' => 0.07,
	'y' => 0.1,
	'w' => (1 - 0.07),
	'h' => 0.8, 
	'draw' => array(
	    'type' => 'report'
	)
    ),
    array(
	'type' => 'font',
	'format' => array(
	    "FontName"=>'calibri',
	    #"FontName"=>PCHART_DIR."fonts/calibri.ttf",
	    "FontSize"=>40,
	)
    ),
    array(
	'type' => 'text',
	'content' => 'title',
	'x' => 0.5,
	'y' => 0.005, 
	'w' => 0.99,
	'h' => 0.1, 	 
	'format' => array(
	    "R"=>0,
	    "G"=>0,
	    "B"=>255,
	    "Align"=>TEXT_ALIGN_TOPMIDDLE
	)
    ),
    array(
	'type' => 'shadow',
	'active' => FALSE, 
    ),
    array(
	'type' => 'text',
	'value' => 'SSG',
	'x' => 0.98,
	'y' => 0.98,
	'w' => 0.3,
	'h' => 0.3, 	
	'format' => array(
	    "R"=>0,
	    "G"=>0,
	    "B"=>0,
	    "Align"=>TEXT_ALIGN_BOTTOMRIGHT,
	    "Alpha"=>35
	)
    )  
);

$THEMES['cisl2'] = array(
    array(
	'type' => 'font',
	'format' => array(
	    "FontName"=>'Forgotte',
	    #"FontName"=>PCHART_DIR."/fonts/Forgotte.ttf",
	    "FontSize"=>14,
	    "R"=>80,
	    "G"=>80,
	    "B"=>80
	),
    ), 
    array(
	'type' => 'rectangle',
	'x' => 0,
	'y' => 0,
	'w' => 0.99,
	'h' => 0.99,
	'format' => array(
	    'R'=>0,
	    "G"=>0,
	    "B"=>0
	)
    ),
    array(
	'type' => 'png',
	'x' => 0,
	'y' => 0,
	'src' => 'img/cisl2.png'
    ),
    array(
	'type' => 'shadow',
	'active' => FALSE,
	'format' => array(
	    "X"=>5,
	    "Y"=>2,
	    "R"=>0,
	    "G"=>0,
	    "B"=>0,
	    "Alpha"=>10
	)
    ),
    array(
	'type' => 'div',
	'x' => 0.07,
	'y' => 0.1,
	'w' => (1 - 0.07),
	'h' => 0.9, 
	'draw' => array(
	    'type' => 'report'
	)
    ),
    array(
	'type' => 'font',
	'format' => array(
	    "FontName"=>'calibri',
	    #"FontName"=>PCHART_DIR."fonts/calibri.ttf",
	    "FontSize"=>40,
	)
    ),
    array(
	'type' => 'text',
	'content' => 'title',
	'x' => 0.5,
	'y' => 0.005, 
	'w' => 0.99,
	'h' => 0.1, 	 
	'format' => array(
	    "R"=>0,
	    "G"=>0,
	    "B"=>255,
	    "Align"=>TEXT_ALIGN_TOPMIDDLE
	)
    ),
    array(
	'type' => 'text',
	'content' => 'batch_nodes',
	'x' => 0.01,
	'y' => 0.10, 
	'w' => 0.1,
	'h' => 0.1, 	 
	'format' => array(
	    "R"=>0,
	    "G"=>0,
	    "B"=>255,
	    "Align"=>TEXT_ALIGN_TOPLEFT
	)
    ), 
    array(
	'type' => 'text',
	'content' => 'batch_cores',
	'x' => 0.01,
	'y' => 0.13, 
	'w' => 0.1,
	'h' => 0.1, 	 
	'format' => array(
	    "R"=>0,
	    "G"=>0,
	    "B"=>255,
	    "Align"=>TEXT_ALIGN_TOPLEFT
	)
    ), 
    array(
	'type' => 'shadow',
	'active' => FALSE, 
    ),
    array(
	'type' => 'text',
	'value' => 'SSG',
	'x' => 0.98,
	'y' => 0.98,
	'w' => 0.3,
	'h' => 0.3, 	
	'format' => array(
	    "R"=>0,
	    "G"=>0,
	    "B"=>0,
	    "Align"=>TEXT_ALIGN_BOTTOMRIGHT,
	    "Alpha"=>35
	)
    )  
);
 

$TEMPLATES['gauss_net'] = array(
   0 => array(
	'type' => 'div',
	'x' => 0.07,
	'y' => 0.0,
	'w' => 0.9,
	'h' => 1-0.07,
	'draw' => array(
	    0 => array(
		'type' => 'shadow',
		'active' => TRUE
		),
	    1 => array(
		'type' => 'div',
		'x' => 0,
		'y' => 0,
		'w' => 1.0,
		'h' => 0.5,
		'draw' => array(
		    '0' => array(
			'type' => 'graph',
			'x' => 0,
			'y' => 0,
			'w' => 0.92,
			'h' => 0.85,
			'format' => array(
			     "R"=>0,
			     "G"=>0,
			     "B"=>0,
			     "Surrounding"=>-200,
			     "Alpha"=>10
			),
			'background' => array(
			    "R"=>0,
			    "G"=>0,
			    "B"=>0,
			    "Surrounding"=>-200,
			    "Alpha"=>10
			),
			'scale' => array(
			    "DrawSubTicks"=>FALSE,
			    "Mode"=>SCALE_MODE_START0,
			    "DrawXLines" => FALSE,
			    'series' => array(
				'max'
			    )   
			),
			'abscissa' => 'Labels',
			'axis' => array(
			    0 => array(
				'units' => 'MB',
				'name' => 'Network Outbound',
			    )
			),
			'series' => array(
			    'oavg' => array(
				'name' => 'Average Net Outbound',
				'palette' => array(
				    "R"=>0,
				    "G"=>100,
				    "B"=>255
				)
			    )
			),
			'draw' => array(
			    0 => array(
				'type' => 'zone',    
				'high' => 'omaxv',
				'low' => 'ominv',
				'format' => array(
				    'LineR' => 0,
				    'LineG' => 0,
				    'LineB' => 255,
				    'LineAlpha' => 10,
				    #'LineTicks' => 10,
				    'AreaR' => 200,
				    'AreaG' => 200,
				    'AreaB' => 255,
				    'AreaAlpha' => 35
				)
			    ),
			    1 => array(
				'type' => 'zone',    
				'high' => 'ostdp1',
				'low' => 'ostdm1',
				'format' => array(
				    'LineR' => 255,
				    'LineG' => 255,
				    'LineB' => 255,
				    'LineAlpha' => 90,
				    'LineTicks' => 2,
				    'AreaR' => 255,
				    'AreaG' => 255,
				    'AreaB' => 255,
				    'AreaAlpha' => 55
				)
			    ),
			    2 => array(
				'type' => 'line',    
				'series' => array('oavg')
			    ) 
			)
		    )
		)
	    ),
	    2 => array(
		'type' => 'div',
		'x' => 0,
		'y' => 0.5,
		'w' => 1.0,
		'h' => 0.5,
		'draw' => array(
		    '0' => array(
			'type' => 'graph',
			'x' => 0,
			'y' => 0,
			'w' => 0.92,
			'h' => 0.85,
			'format' => array(
			     "R"=>0,
			     "G"=>0,
			     "B"=>0,
			     "Surrounding"=>-200,
			     "Alpha"=>10
			),
			'background' => array(
			    "R"=>0,
			    "G"=>0,
			    "B"=>0,
			    "Surrounding"=>-200,
			    "Alpha"=>10
			),
			'scale' => array(
			    "DrawSubTicks"=>FALSE,
			    "Mode"=>SCALE_MODE_START0,
			    "DrawXLines" => FALSE,
			    #"DrawYLines" => FALSE
			),
			'abscissa' => 'Labels',
			'axis' => array(
			    0 => array(
				'units' => 'MB',
				'name' => 'Network Inbound',
			    )
			),
			'series' => array(
			    'iavg' => array(
				'name' => 'Average Net Inbound',
				'palette' => array(
				    "R"=>0,
				    "G"=>100,
				    "B"=>255
				)
			    )
			),
			'draw' => array(
			    0 => array(
				'type' => 'zone',    
				'high' => 'imaxv',
				'low' => 'iminv',
				'format' => array(
				    'LineR' => 0,
				    'LineG' => 0,
				    'LineB' => 255,
				    'LineAlpha' => 10,
				    #'LineTicks' => 10,
				    'AreaR' => 200,
				    'AreaG' => 200,
				    'AreaB' => 255,
				    'AreaAlpha' => 35
				)
			    ),
			    1 => array(
				'type' => 'zone',    
				'high' => 'istdp1',
				'low' => 'istdm1',
				'format' => array(
				    'LineR' => 255,
				    'LineG' => 255,
				    'LineB' => 255,
				    'LineAlpha' => 90,
				    'LineTicks' => 2,
				    'AreaR' => 255,
				    'AreaG' => 255,
				    'AreaB' => 255,
				    'AreaAlpha' => 55
				)
			    ),
			    2 => array(
				'type' => 'line',    
				'series' => array('iavg')
			    ) 
			)
		    )
		)
	    )
	)
    ),
    1 => array(
	'type' => 'font',
	'format' => array(
	    #'FontName' => PCHART_DIR."/fonts/pf_arma_five.ttf",
	    'FontName' => 'pf_arma_five',
	    'FondSize' => 6
	)
    ),
    2 => array(
	'type' => 'legend',
	'x' => 0.50,
	'y' => 0.95, 
	'series' => array(
	    'iavg', 'oavg'
	),
	'format' => array(
	    "Style" => LEGEND_ROUND,
	    "Mode" => LEGEND_HORIZONTAL,
	    "Family" => LEGEND_FAMILY_CIRCLE,
	    "FontSize" => 12,
	    "FontR" => 0,
	    "FontG" => 0,
	    "FontB" => 255,
	    "Alpha" => 50
	)
    )
);

$TEMPLATES['gauss_flop'] = array(
   0 => array(
	'type' => 'div',
	'x' => 0.09,
	'y' => 0.0,
	'w' => 1.0-0.09,
	'h' => 0.95,
	'draw' => array(
	    0 => array(
		'type' => 'shadow',
		'active' => TRUE
		),
	    1 => array(
		'type' => 'graph',
		'x' => 0,
		'y' => 0,
		'w' => 0.92,
		'h' => 0.85,
		'format' => array(
		     "R"=>0,
		     "G"=>0,
		     "B"=>0,
		     "Surrounding"=>-200,
		     "Alpha"=>10
		),
		'background' => array(
		    "R"=>0,
		    "G"=>0,
		    "B"=>0,
		    "Surrounding"=>-200,
		    "Alpha"=>10
		),
		'scale' => array(
		    "DrawSubTicks"=>FALSE,
		    "Mode"=>SCALE_MODE_START0,
		    "DrawXLines" => FALSE,
		    'series' => array(
			'max'
		    )   
		),
		'abscissa' => 'Labels',
		'axis' => array(
		    0 => array(
			#'units' => '',
			'name' => 'MFLOP/s',
		    )
		),
		'series' => array(
		    'avg' => array(
			'name' => 'Average MFLOP/s',
			'palette' => array(
			    "R"=>0,
			    "G"=>100,
			    "B"=>255
			)
		    )
		),
		'draw' => array(
		    0 => array(
			'type' => 'zone',    
			'high' => 'maxv',
			'low' => 'minv',
			'format' => array(
			    'LineR' => 0,
			    'LineG' => 0,
			    'LineB' => 255,
			    'LineAlpha' => 10,
			    #'LineTicks' => 10,
			    'AreaR' => 200,
			    'AreaG' => 200,
			    'AreaB' => 255,
			    'AreaAlpha' => 35
			)
		    ),
		    1 => array(
			'type' => 'zone',    
			'high' => 'stdp1',
			'low' => 'stdm1',
			'format' => array(
			    'LineR' => 255,
			    'LineG' => 255,
			    'LineB' => 255,
			    'LineAlpha' => 90,
			    'LineTicks' => 2,
			    'AreaR' => 255,
			    'AreaG' => 255,
			    'AreaB' => 255,
			    'AreaAlpha' => 55
			)
		    ),
		    2 => array(
			'type' => 'line',    
			'series' => array('avg')
		    ) 
		)
	    )
	)
    ),
    1 => array(
	'type' => 'font',
	'format' => array(
	    #'FontName' => PCHART_DIR."/fonts/pf_arma_five.ttf",
	    'FontName' => 'pf_arma_five',
	    'FondSize' => 6
	)
    ),
    2 => array(
	'type' => 'legend',
	'x' => 0.50,
	'y' => 0.95, 
	'series' => array(
	    'avg'
	),
	'format' => array(
	    "Style" => LEGEND_ROUND,
	    "Mode" => LEGEND_HORIZONTAL,
	    "Family" => LEGEND_FAMILY_CIRCLE,
	    "FontSize" => 12,
	    "FontR" => 0,
	    "FontG" => 0,
	    "FontB" => 255,
	    "Alpha" => 50
	)
    )
); 

$TEMPLATES['gauss_cpu'] = array(
   0 => array(
	'type' => 'div',
	'x' => 0.07,
	'y' => 0.0,
	'w' => 1.0-0.07,
	'h' => 0.95,
	'draw' => array(
	    0 => array(
		'type' => 'shadow',
		'active' => TRUE
		),
	    1 => array(
		'type' => 'graph',
		'x' => 0,
		'y' => 0,
		'w' => 0.92,
		'h' => 0.85,
		'format' => array(
		     "R"=>0,
		     "G"=>0,
		     "B"=>0,
		     "Surrounding"=>-200,
		     "Alpha"=>10
		),
		'background' => array(
		    "R"=>0,
		    "G"=>0,
		    "B"=>0,
		    "Surrounding"=>-200,
		    "Alpha"=>10
		),
		'scale' => array(
		    "DrawSubTicks"=>FALSE,
		    "Mode"=>SCALE_MODE_START0,
		    "DrawXLines" => FALSE,
		    'series' => array(
			'max'
		    )  
		),
		'abscissa' => 'Labels',
		'axis' => array(
		    0 => array(
			#'units' => '',
			'name' => 'CPU Load',
		    )
		),
		'series' => array(
		    'avg' => array(
			'name' => 'Average CPU Load',
			'palette' => array(
			    "R"=>0,
			    "G"=>100,
			    "B"=>255
			)
		    )
		),
		'draw' => array(
		    0 => array(
			'type' => 'zone',    
			'high' => 'maxv',
			'low' => 'minv',
			'format' => array(
			    'LineR' => 0,
			    'LineG' => 0,
			    'LineB' => 255,
			    'LineAlpha' => 10,
			    #'LineTicks' => 10,
			    'AreaR' => 200,
			    'AreaG' => 200,
			    'AreaB' => 255,
			    'AreaAlpha' => 35
			)
		    ),
		    1 => array(
			'type' => 'zone',    
			'high' => 'stdp1',
			'low' => 'stdm1',
			'format' => array(
			    'LineR' => 255,
			    'LineG' => 255,
			    'LineB' => 255,
			    'LineAlpha' => 90,
			    'LineTicks' => 2,
			    'AreaR' => 255,
			    'AreaG' => 255,
			    'AreaB' => 255,
			    'AreaAlpha' => 55
			)
		    ),
		    2 => array(
			'type' => 'line',    
			'series' => array('avg')
		    ) 
		)
	    )
	)
    ),
    1 => array(
	'type' => 'font',
	'format' => array(
	    #'FontName' => PCHART_DIR."/fonts/pf_arma_five.ttf",
	    'FontName' => 'pf_arma_five',
	    'FondSize' => 6
	)
    ),
    2 => array(
	'type' => 'legend',
	'x' => 0.50,
	'y' => 0.95, 
	'series' => array(
	    'avg'
	),
	'format' => array(
	    "Style" => LEGEND_ROUND,
	    "Mode" => LEGEND_HORIZONTAL,
	    "Family" => LEGEND_FAMILY_CIRCLE,
	    "FontSize" => 12,
	    "FontR" => 0,
	    "FontG" => 0,
	    "FontB" => 255,
	    "Alpha" => 50
	)
    )
); 

$TEMPLATES['gauss_mem'] = array(
   0 => array(
	'type' => 'div',
	'x' => 0.07,
	'y' => 0.0,
	'w' => 1.0-0.07,
	'h' => 0.95,
	'draw' => array(
	    0 => array(
		'type' => 'shadow',
		'active' => TRUE
		),
	    1 => array(
		'type' => 'graph',
		'x' => 0,
		'y' => 0,
		'w' => 0.92,
		'h' => 0.85,
		'format' => array(
		     "R"=>0,
		     "G"=>0,
		     "B"=>0,
		     "Surrounding"=>-200,
		     "Alpha"=>10
		),
		'background' => array(
		    "R"=>0,
		    "G"=>0,
		    "B"=>0,
		    "Surrounding"=>-200,
		    "Alpha"=>10
		),
		'scale' => array(
		    "DrawSubTicks"=>FALSE,
		    "Mode"=>SCALE_MODE_START0,
		    "DrawXLines" => FALSE,
		    #"DrawYLines" => FALSE
		    'series' => array(
			'^max$'
		    ) 
		),
		'abscissa' => 'Labels',
		'axis' => array(
		    0 => array(
			'units' => 'GB',
			'name' => 'Memory Usage',
		    )
		),
		'series' => array(
		    'avg' => array(
			'name' => 'Average Memory Usage',
			'palette' => array(
			    "R"=>0,
			    "G"=>100,
			    "B"=>255
			)
		    ),
		    'max' => array(
			'name' => 'Maxium Memory',
			'palette' => array(
			    "R"=>255,
			    "G"=>0,
			    "B"=>0
			)
		    ) 
		),
		'draw' => array(
		    0 => array(
			'type' => 'zone',    
			'high' => 'maxv',
			'low' => 'minv',
			'format' => array(
			    'LineR' => 0,
			    'LineG' => 0,
			    'LineB' => 255,
			    'LineAlpha' => 10,
			    #'LineTicks' => 10,
			    'AreaR' => 200,
			    'AreaG' => 200,
			    'AreaB' => 255,
			    'AreaAlpha' => 35
			)
		    ),
		    1 => array(
			'type' => 'zone',    
			'high' => 'stdp1',
			'low' => 'stdm1',
			'format' => array(
			    'LineR' => 255,
			    'LineG' => 255,
			    'LineB' => 255,
			    'LineAlpha' => 90,
			    'LineTicks' => 2,
			    'AreaR' => 255,
			    'AreaG' => 255,
			    'AreaB' => 255,
			    'AreaAlpha' => 55
			)
		    ),
		    2 => array(
			'type' => 'line',    
			'series' => array('^avg$', '^max$')
		    ) 
		)
	    )
	)
    ),
    1 => array(
	'type' => 'font',
	'format' => array(
	    #'FontName' => PCHART_DIR."/fonts/pf_arma_five.ttf",
	    'FontName' => 'pf_arma_five',
	    'FondSize' => 6
	)
    ),
    2 => array(
	'type' => 'legend',
	'x' => 0.50,
	'y' => 0.95, 
	'series' => array(
	    'avg', '^max$'
	),
	'format' => array(
	    "Style" => LEGEND_ROUND,
	    "Mode" => LEGEND_HORIZONTAL,
	    "Family" => LEGEND_FAMILY_CIRCLE,
	    "FontSize" => 12,
	    "FontR" => 0,
	    "FontG" => 0,
	    "FontB" => 255,
	    "Alpha" => 50
	)
    )
);   

$TEMPLATES['topuser_pie'] = array(
    array(
	'type' => '3dpie',
	'x' => 0.5,
	'y' => 0.5,
	'r' => 0.45,
	'abscissa' => 'UserName',
	'values' => 'CPUhr',
	'series' => array(
	    'UserName' => array(
		'name' => 'User Name',
	    ),
	    'CPUhr' => array(
		'name' => 'Number of CPU Hours',
	    )
	), 
	'format' => array(
            "SecondPass"=>TRUE,
	    "DrawLabels"=>TRUE,
	    "WriteValues"=>FALSE,
	    "LabelStacked"=>TRUE,
	    "Border"=>TRUE,
	    #"Radius"=>min($w,$h)*0.37, set by tpl
	    "ValuePosition"=>"PIE_VALUE_INSIDE",
	    "DataGapAngle"=>0,
	    "LabelStacked"=>TRUE,
	    "LabelR"=>40,
	    "LabelG"=>0,
	    "LabelB"=>0,
	    "LabelAlpha"=>90,
	    "ValueR"=>0,
	    "ValueG"=>10,
	    "ValueB"=>100,
	    "ValueAlphaA"=>90
	)
    )
);

$TEMPLATES['topuser_stack'] = array(
    array(
	'type' => 'graph',
	'x' => 0.1,
	'y' => 0,
	'w' => 0.75,
	'h' => 0.90,
	'format' => array(
	     "R"=>0,
	     "G"=>0,
	     "B"=>0,
	     "Surrounding"=>-200,
	     "Alpha"=>10
	),
	'background' => array(
	    "R"=>0,
	    "G"=>0,
	    "B"=>0,
	    "Surrounding"=>-200,
	    "Alpha"=>10
	),
	'scale' => array(
	    "DrawSubTicks"=>FALSE,
	    "Mode"=>SCALE_MODE_ADDALL_START0,
	    "DrawXLines" => FALSE,
	    #"DrawYLines" => FALSE,
	    'series' => array(
		'^CPUs\.*',
		'^\[others\]$'
	    )
	),
	'abscissa' => 'Labels',
	'axis' => array(
	    0 => array(
		'name' => 'CPUs',
	    )
	),
	'series' => array(
	    'labels' => array(
		'name' => 'Date',
	    ),
            '[others]' => array(
		'name' => 'Others',
	    ) 
	),
	'draw' => array(
	    0 => array(
		'type' => 'stack',
		'series' => array(
		    '^CPUs\.*',
		    '^\[others\]$'
		),
		'format' => array(
		    "DisplayValues"=>FALSE,
		    "DisplayColor"=>DISPLAY_AUTO,
		    "Surrounding"=>20
		)
	    )
	)
    ),
    array(
	'type' => 'legend',
	'x' => 0.90,
	'y' => 0.05, 
	'series' => array(
	    '^CPUs\.*',
	    '^\[others\]$'
	),
	'format' => array(
	    "Style" => LEGEND_ROUND,
	    "Mode" => LEGEND_VERTICAL,
	    "Family" => LEGEND_FAMILY_CIRCLE,
	    "FontSize" => 12,
	    "FontR" => 0,
	    "FontG" => 0,
	    "FontB" => 255,
	    "Alpha" => 50
	) 
    )
);   

$TEMPLATES['fairshare_stack'] = array(
    array(
	'type' => 'graph',
	'x' => 0.1,
	'y' => 0,
	'w' => 0.75,
	'h' => 0.90,
	'format' => array(
	     "R"=>0,
	     "G"=>0,
	     "B"=>0,
	     "Surrounding"=>-200,
	     "Alpha"=>10
	),
	'background' => array(
	    "R"=>0,
	    "G"=>0,
	    "B"=>0,
	    "Surrounding"=>-200,
	    "Alpha"=>10
	),
	'scale' => array(
	    "DrawSubTicks"=>FALSE,
	    "Mode"=>SCALE_MODE_ADDALL_START0,
	    "DrawXLines" => FALSE,
	    #"DrawYLines" => FALSE,
	    'series' => array(
		'^pgroup\.*'
	    )
	),
	'abscissa' => 'Labels',
	'axis' => array(
	    0 => array(
		'name' => 'CPUs',
	    )
	),
	'series' => array(
	    'labels' => array(
		'name' => 'Date',
	    )
	),
	'draw' => array(
	    0 => array(
		'type' => 'stack',
		'series' => array(
		    '^pgroup\.*'
		),
		'format' => array(
		    "DisplayValues"=>FALSE,
		    "DisplayColor"=>DISPLAY_AUTO,
		    "Surrounding"=>20
		)
	    )
	)
    ),
    array(
	'type' => 'legend',
	'x' => 0.90,
	'y' => 0.05, 
	'series' => array(
	    '^pgroup\.*'
	),
	'format' => array(
	    "Style" => LEGEND_ROUND,
	    "Mode" => LEGEND_VERTICAL,
	    "Family" => LEGEND_FAMILY_CIRCLE,
	    "FontSize" => 12,
	    "FontR" => 0,
	    "FontG" => 0,
	    "FontB" => 255,
	    "Alpha" => 50
	) 
    )
);    

$TEMPLATES['fairshare_pie'] = array(
    array(
	'type' => '3dpie',
	'x' => 0.25,
	'y' => 0.5,
	'r' => 0.22,
	'abscissa' => 'cluster.facility',
	'values' => 'cluster.cpuHr',
	'format' => array(
            "SecondPass"=>TRUE,
	    "DrawLabels"=>TRUE,
	    "WriteValues"=>TRUE,
	    "LabelStacked"=>TRUE,
	    "Border"=>TRUE,
	    "ValuePosition"=>"PIE_VALUE_INSIDE",
	    "DataGapAngle"=>0,
	    "LabelStacked"=>TRUE,
	    "LabelR"=>40,
	    "LabelG"=>0,
	    "LabelB"=>0,
	    "LabelAlpha"=>90,
	    "ValueR"=>0,
	    "ValueG"=>10,
	    "ValueB"=>100,
	    "ValueAlphaA"=>90
	)
    ),
    array(
	'type' => '3dpie',
	'x' => 0.75,
	'y' => 0.2,
	'r' => 0.12,
	'abscissa' => 'com.pgroup',
	'values' => 'com.cpuHr',
	'format' => array(
            "SecondPass"=>TRUE,
	    "DrawLabels"=>TRUE,
	    "WriteValues"=>TRUE,
	    "LabelStacked"=>TRUE,
	    "Border"=>TRUE,
	    "ValuePosition"=>"PIE_VALUE_INSIDE",
	    "DataGapAngle"=>0,
	    "LabelStacked"=>TRUE,
	    "LabelR"=>40,
	    "LabelG"=>0,
	    "LabelB"=>0,
	    "LabelAlpha"=>90,
	    "ValueR"=>0,
	    "ValueG"=>10,
	    "ValueB"=>100,
	    "ValueAlphaA"=>90
	)
    ),
    array(
	'type' => '3dpie',
	'x' => 0.75,
	'y' => 0.5,
	'r' => 0.12,
	'abscissa' => 'csl.pgroup',
	'values' => 'csl.cpuHr',
	'format' => array(
            "SecondPass"=>TRUE,
	    "DrawLabels"=>TRUE,
	    "WriteValues"=>TRUE,
	    "LabelStacked"=>TRUE,
	    "Border"=>TRUE,
	    "ValuePosition"=>"PIE_VALUE_INSIDE",
	    "DataGapAngle"=>0,
	    "LabelStacked"=>TRUE,
	    "LabelR"=>40,
	    "LabelG"=>0,
	    "LabelB"=>0,
	    "LabelAlpha"=>90,
	    "ValueR"=>0,
	    "ValueG"=>10,
	    "ValueB"=>100,
	    "ValueAlphaA"=>90
	)
    ), 
    array(
	'type' => '3dpie',
	'x' => 0.75,
	'y' => 0.82,
	'r' => 0.12,
	'abscissa' => 'asd.pgroup',
	'values' => 'asd.cpuHr',
	'format' => array(
            "SecondPass"=>TRUE,
	    "DrawLabels"=>TRUE,
	    "WriteValues"=>TRUE,
	    "LabelStacked"=>TRUE,
	    "Border"=>TRUE,
	    "ValuePosition"=>"PIE_VALUE_INSIDE",
	    "DataGapAngle"=>0,
	    "LabelStacked"=>TRUE,
	    "LabelR"=>40,
	    "LabelG"=>0,
	    "LabelB"=>0,
	    "LabelAlpha"=>90,
	    "ValueR"=>0,
	    "ValueG"=>10,
	    "ValueB"=>100,
	    "ValueAlphaA"=>90
	)
    ),  
    array(
	'type' => 'font',
	'format' => array(
	    "FontName"=>'calibri',
	    "FontSize"=>35
	)
    ),
    array(
	'type' => 'text',
	'content' => 'cluster',
	'x' => 0.25,
	'y' => 0.20, 
	'w' => 0.4,
	'h' => 0.2, 	
	'format' => array(
	    "R"=>0,
	    "G"=>0,
	    "B"=>0,
	    "Align"=>TEXT_ALIGN_TOPMIDDLE
	)
    ),
    array(
	'type' => 'font',
	'format' => array(
	    "FontName"=>'calibri',
	    "FontSize"=>30
	)
    ),     
    array(
	'type' => 'text',
	'value' => 'Community',
	'require' => 'com.cpuHr',
	'x' => 0.75,
	'y' => 0.015, 
	'w' => 0.2,
	'h' => 0.2, 	
	'format' => array(
	    "R"=>0,
	    "G"=>0,
	    "B"=>0,
	    "Align"=>TEXT_ALIGN_TOPMIDDLE
	)
    ),
    array(
	'type' => 'text',
	'value' => 'CISL',
	'require' => 'csl.cpuHr',
	'x' => 0.75,
	'y' => 0.33, 
	'w' => 0.4,
	'h' => 0.2, 	
	'format' => array(
	    "R"=>0,
	    "G"=>0,
	    "B"=>0,
	    "Align"=>TEXT_ALIGN_TOPMIDDLE
	)
    ),
    array(
	'type' => 'text',
	'value' => 'ASD',
	'require' => 'asd.cpuHr',
	'x' => 0.75,
	'y' => 0.65, 
	'w' => 0.4,
	'h' => 0.2, 	
	'format' => array(
	    "R"=>0,
	    "G"=>0,
	    "B"=>0,
	    "Align"=>TEXT_ALIGN_TOPMIDDLE
	)
    )
);

$TEMPLATES['queue_wait_stack'] = array(
    array(
	'type' => 'graph',
	'x' => 0.1,
	'y' => 0,
	'w' => 0.75,
	'h' => 0.90,
	'format' => array(
	     "R"=>0,
	     "G"=>0,
	     "B"=>0,
	     "Surrounding"=>-200,
	     "Alpha"=>10
	),
	'background' => array(
	    "R"=>0,
	    "G"=>0,
	    "B"=>0,
	    "Surrounding"=>-200,
	    "Alpha"=>10
	),
	'scale' => array(
	    "DrawSubTicks"=>FALSE,
	    "Mode"=>SCALE_MODE_ADDALL_START0,
	    "DrawXLines" => FALSE,
	    #"DrawYLines" => FALSE,
	    'series' => array(
		'^queue\.*'
	    )
	),
	'abscissa' => 'Labels',
	'axis' => array(
	    0 => array(
		'name' => 'Jobs',
	    )
	),
	'series' => array(
	    'labels' => array(
		'name' => 'Date',
	    )
	),
	'draw' => array(
	    0 => array(
		'type' => 'stack',
		'series' => array(
		    '^queue\.*'
		),
		'format' => array(
		    "DisplayValues"=>FALSE,
		    "DisplayColor"=>DISPLAY_AUTO,
		    "Surrounding"=>20
		)
	    )
	)
    ),
    array(
	'type' => 'legend',
	'x' => 0.87,
	'y' => 0.05, 
	'series' => array(
	    '^queue\.*'
	),
	'format' => array(
	    "Style" => LEGEND_ROUND,
	    "Mode" => LEGEND_VERTICAL,
	    "Family" => LEGEND_FAMILY_CIRCLE,
	    "FontSize" => 12,
	    "FontR" => 0,
	    "FontG" => 0,
	    "FontB" => 255,
	    "Alpha" => 50
	) 
    )
);   

$TEMPLATES['queue_wait_avg_stack'] = array(
    array(
	'type' => 'graph',
	'x' => 0.1,
	'y' => 0,
	'w' => 0.75,
	'h' => 0.90,
	'format' => array(
	     "R"=>0,
	     "G"=>0,
	     "B"=>0,
	     "Surrounding"=>-200,
	     "Alpha"=>10
	),
	'background' => array(
	    "R"=>0,
	    "G"=>0,
	    "B"=>0,
	    "Surrounding"=>-200,
	    "Alpha"=>10
	),
	'scale' => array(
	    "DrawSubTicks"=>FALSE,
	    "Mode"=>SCALE_MODE_ADDALL_START0,
	    "DrawXLines" => FALSE,
	    #"DrawYLines" => FALSE,
	    'series' => array(
		'^queue\.*'
	    )
	),
	'abscissa' => 'Labels',
	'axis' => array(
	    0 => array(
		'name' => 'Minutes',
	    )
	),
	'series' => array(
	    'labels' => array(
		'name' => 'Date',
	    )
	),
	'draw' => array(
	    0 => array(
		'type' => 'stack',
		'series' => array(
		    '^queue\.*'
		),
		'format' => array(
		    "DisplayValues"=>FALSE,
		    "DisplayColor"=>DISPLAY_AUTO,
		    "Surrounding"=>20
		)
	    )
	)
    ),
    array(
	'type' => 'legend',
	'x' => 0.87,
	'y' => 0.05, 
	'series' => array(
	    '^queue\.*'
	),
	'format' => array(
	    "Style" => LEGEND_ROUND,
	    "Mode" => LEGEND_VERTICAL,
	    "Family" => LEGEND_FAMILY_CIRCLE,
	    "FontSize" => 12,
	    "FontR" => 0,
	    "FontG" => 0,
	    "FontB" => 255,
	    "Alpha" => 50
	) 
    )
);

$TEMPLATES['queue_wait_avg_freq_stack'] = array(
    array(
	'type' => 'graph',
	'x' => 0.1,
	'y' => 0,
	'w' => 0.75,
	'h' => 0.90,
	'format' => array(
	     "R"=>0,
	     "G"=>0,
	     "B"=>0,
	     "Surrounding"=>-200,
	     "Alpha"=>10
	),
	'background' => array(
	    "R"=>0,
	    "G"=>0,
	    "B"=>0,
	    "Surrounding"=>-200,
	    "Alpha"=>10
	),
	'scale' => array(
	    "DrawSubTicks"=>FALSE,
	    "Mode"=>SCALE_MODE_ADDALL_START0,
	    "DrawXLines" => FALSE,
	    #"DrawYLines" => FALSE,
	    'series' => array(
		'^tfreq\.*'
	    )
	),
	'abscissa' => 'Labels',
	'axis' => array(
	    0 => array(
		'name' => 'Jobs',
	    )
	),
	'series' => array(
	    'Labels' => array(
		'name' => 'Time Frequency Interval',
	    )
	),
	'draw' => array(
	    0 => array(
		'type' => 'stack',
		'series' => array(
		    '^tfreq\.*'
		),
		'format' => array(
		    "DisplayValues"=>FALSE,
		    "DisplayColor"=>DISPLAY_AUTO,
		    "Surrounding"=>20
		)
	    )
	)
    ),
    array(
	'type' => 'legend',
	'x' => 0.87,
	'y' => 0.05, 
	'series' => array(
	    '^tfreq\.*'
	),
	'format' => array(
	    "Style" => LEGEND_ROUND,
	    "Mode" => LEGEND_VERTICAL,
	    "Family" => LEGEND_FAMILY_CIRCLE,
	    "FontSize" => 12,
	    "FontR" => 0,
	    "FontG" => 0,
	    "FontB" => 255,
	    "Alpha" => 50
	) 
    )
);

$TEMPLATES['util_pe_hist'] = array(
    array(
	'type' => 'graph',
	'x' => 0.1,
	'y' => 0,
	'w' => 0.75,
	'h' => 0.83,
	'format' => array(
	     "R"=>0,
	     "G"=>0,
	     "B"=>0,
	     "Surrounding"=>-200,
	     "Alpha"=>10
	),
	'background' => array(
	    "R"=>0,
	    "G"=>0,
	    "B"=>0,
	    "Surrounding"=>-200,
	    "Alpha"=>10
	),
	'scale' => array(
	    "LabelRotation" => 90,
	    "DrawSubTicks"=>FALSE,
	    "Mode"=>SCALE_MODE_ADDALL_START0,
	    "DrawXLines" => FALSE,
	    #"DrawYLines" => FALSE,
	    'series' => array(
		'jobs'
	    )
	),
	'abscissa' => 'Labels',
	'axis' => array(
	    0 => array(
		'name' => 'Jobs',
	    )
	),
	'series' => array(
	    'Labels' => array(
		'name' => 'PE Frequency Interval',
	    )
	),
	'draw' => array(
	    array(
		'type' => 'bar',
		'series' => array(
		    '^jobs*'
		),
		'format' => array(
		    "DisplayValues"=>FALSE,
		    "DisplayColor"=>DISPLAY_AUTO,
		    "Surrounding"=>20
		)
	    )
	)
    ),
    array(
	'type' => 'legend',
	'x' => 0.87,
	'y' => 0.05, 
	'series' => array(
	    '^jobs*'
	),
	'format' => array(
	    "Style" => LEGEND_ROUND,
	    "Mode" => LEGEND_VERTICAL,
	    "Family" => LEGEND_FAMILY_CIRCLE,
	    "FontSize" => 12,
	    "FontR" => 0,
	    "FontG" => 0,
	    "FontB" => 255,
	    "Alpha" => 50
	) 
    )
); 

$TEMPLATES['util_pe_hr_hist'] = array(
    array(
	'type' => 'graph',
	'x' => 0.1,
	'y' => 0,
	'w' => 0.75,
	'h' => 0.83,
	'format' => array(
	     "R"=>0,
	     "G"=>0,
	     "B"=>0,
	     "Surrounding"=>-200,
	     "Alpha"=>10
	),
	'background' => array(
	    "R"=>0,
	    "G"=>0,
	    "B"=>0,
	    "Surrounding"=>-200,
	    "Alpha"=>10
	),
	'scale' => array(
	    "LabelRotation" => 90,
	    "DrawSubTicks"=>FALSE,
	    "Mode"=>SCALE_MODE_ADDALL_START0,
	    "DrawXLines" => FALSE,
	    #"DrawYLines" => FALSE,
	    'series' => array(
		'^cpu',
		'^job'
	    )
	),
	'abscissa' => 'Labels',
	'axis' => array(
	    0 => array(
		'name' => 'CPU Hours',
	    )
	),
	'series' => array(
	    'Labels' => array(
		'name' => 'PE Frequency Interval',
	    ),
	    'cpuhrs' => array(
		'name' => 'CPU Hours Assigned',
	    ),
	    'cputime' => array(
		'name' => 'CPU Time Actual',
	    ) 
	),
	'draw' => array(
	    array(
		'type' => 'stack',
		'series' => array(
		    '^cpuhrs$'
		),
		'format' => array(
		    "DisplayValues"=>FALSE,
		    "DisplayColor"=>DISPLAY_AUTO,
		    "Surrounding"=>20
		)
	    ),
	    array(
		'type' => 'stack',
		'series' => array(
		    '^cputime$'
		),
		'format' => array(
		    "DisplayValues"=>FALSE,
		    "DisplayColor"=>DISPLAY_AUTO,
		    "Surrounding"=>20
		)
	    )
	)
    ),
    array(
	'type' => 'legend',
	'x' => 0.87,
	'y' => 0.05, 
	'series' => array(
	    '^cputime',
	    '^cpuhrs'
	),
	'format' => array(
	    "Style" => LEGEND_ROUND,
	    "Mode" => LEGEND_VERTICAL,
	    "Family" => LEGEND_FAMILY_CIRCLE,
	    "FontSize" => 12,
	    "FontR" => 0,
	    "FontG" => 0,
	    "FontB" => 255,
	    "Alpha" => 50
	) 
    )
); 

$TEMPLATES['util_pe_phr_hist'] = array(
    array(
	'type' => 'graph',
	'x' => 0.1,
	'y' => 0,
	'w' => 0.75,
	'h' => 0.83,
	'format' => array(
	     "R"=>0,
	     "G"=>0,
	     "B"=>0,
	     "Surrounding"=>-200,
	     "Alpha"=>10
	),
	'background' => array(
	    "R"=>0,
	    "G"=>0,
	    "B"=>0,
	    "Surrounding"=>-200,
	    "Alpha"=>10
	),
	'scale' => array(
	    "LabelRotation" => 90,
	    "DrawSubTicks"=>FALSE,
 	    "Mode"=>SCALE_MODE_MANUAL,
	    "YMax" => 'YMax',
	    "YMin" => 'YMin',
	    "XMargin" => 0,
	    "YMargin" => 0,
	    "DrawXLines" => FALSE,
	    #"DrawYLines" => FALSE,
	    'series' => array(
		'^cpu',
		'^job'
	    )
	),
	'abscissa' => 'Labels',
	'axis' => array(
	    0 => array(
		'name' => 'Percent',
	    )
	),
	'series' => array(
	    'Labels' => array(
		'name' => 'PE Frequency Interval',
	    ),
	    'cpuhrs' => array(
		'name' => 'CPU Hours Assigned',
	    ),
	    'cputime' => array(
		'name' => 'CPU Time Actual',
	    ),
	    'jobs' => array(
		'name' => 'Jobs',
	    )                                
	),
	'draw' => array(
	    array(
		'type' => 'stack',
		'series' => array(
		    '^cpuhrs$'
		),
		'format' => array(
		    "DisplayValues"=>FALSE,
		    "DisplayColor"=>DISPLAY_AUTO,
		    "Surrounding"=>20
		)
	    ),
	    array(
		'type' => 'stack',
		'series' => array(
		    '^cputime$'
		),
		'format' => array(
		    "DisplayValues"=>FALSE,
		    "DisplayColor"=>DISPLAY_AUTO,
		    "Surrounding"=>20
		)
	    ),
 	    array(
		'type' => 'line',    
		'series' => array('^jobs$'),
 		'format' => array(
		    "Weight"=>5
		) 
	    )   
	)
    ),
    array(
	'type' => 'legend',
	'x' => 0.87,
	'y' => 0.05, 
	'series' => array(
	    '^cputime',
	    '^cpuhrs',
	    '^jobs$'
	),
	'format' => array(
	    "Style" => LEGEND_ROUND,
	    "Mode" => LEGEND_VERTICAL,
	    "Family" => LEGEND_FAMILY_CIRCLE,
	    "FontSize" => 12,
	    "FontR" => 0,
	    "FontG" => 0,
	    "FontB" => 255,
	    "Alpha" => 50
	) 
    )
); 
  

$TEMPLATES['mem_util_stack'] = array(
    array(
	'type' => 'graph',
	'x' => 0.1,
	'y' => 0,
	'w' => 0.72,
	'h' => 0.90,
	'format' => array(
	     "R"=>0,
	     "G"=>0,
	     "B"=>0,
	     "Surrounding"=>-200,
	     "Alpha"=>10
	),
	'background' => array(
	    "R"=>0,
	    "G"=>0,
	    "B"=>0,
	    "Surrounding"=>-200,
	    "Alpha"=>10
	),
	'scale' => array(
	    "DrawSubTicks"=>FALSE,
	    "Mode"=>SCALE_MODE_MANUAL,
	    "YMax" => 'YMax',
	    "YMin" => 'YMin',
	    "XMargin" => 0,
	    "YMargin" => 0,
	    "DrawXLines" => FALSE,
	    #"DrawYLines" => FALSE,
	    'series' => array(
		'^memtotal$'
	    )
	),
	'abscissa' => 'Labels',
	'axis' => array(
	    0 => array(
		'name' => 'Memory (GB)',
	    )
	),
	'series' => array(
	    'Labels' => array(
		'name' => 'Date',
	    ),
 	    'memtotal' => array(
		'name' => 'Total Physical Memory',
	    ),
 	    'memused' => array(
		'name' => 'Process Used Memory',
		'palette' => array(
		    "R"=>0,
		    "G"=>100,
		    "B"=>255,
		    "Alpha"=>75
		) 
	    ),
	    'memcached' => array(
		'name' => 'Cached Memory',
		'palette' => array(
		    "R"=>40,
		    "G"=>100,
		    "B"=>255,
		    "Alpha"=>55
		) 
	    ), 
  	    'actcpus' => array(
		'name' => 'CPUs Available',
		'palette' => array(
		    "R"=>0,
		    "G"=>100,
		    "B"=>0,
		    "Alpha"=>15
		)  	    
	    ), 
   	    'usercpus' => array(
		'name' => 'User CPU Usage',
	    ),  
   	    'nicecpus' => array(
		'name' => 'Nice CPU usage',
	    ),  
    	    'wiocpus' => array(
		'name' => 'Wait I/O CPU Usage',
	    ),  
    	    'systemcpus' => array(
		'name' => 'System CPU Usage',
	    )
	),
	'draw' => array(
            array(
	        'type' => 'stack',
	        'series' => array(
	            '^mem(used|cached)$'
	        ),
	        'format' => array(
	            "DisplayValues"=>FALSE,
	            "DisplayColor"=>DISPLAY_AUTO,
	            "Surrounding"=>20
	        )
	    ),                  
	    array(
		'type' => 'line',    
		'series' => array('^memtotal$'),
 		'format' => array(
		    "Weight"=>5
		) 
	    )
	)
    ),
    array(
	'type' => 'legend',
	'x' => 0.845,
	'y' => 0.05, 
	'series' => array(
	    'memtotal',
	    '^mem(used|cached)$'
	),
	'format' => array(
	    "Style" => LEGEND_ROUND,
	    "Mode" => LEGEND_VERTICAL,
	    "Family" => LEGEND_FAMILY_CIRCLE,
	    "FontSize" => 12,
	    "FontR" => 0,
	    "FontG" => 0,
	    "FontB" => 255,
	    "Alpha" => 50
	) 
    )
); 

$TEMPLATES['util_mem_stack_histogram'] = array(
    array(
	'type' => 'graph',
	'x' => 0.1,
	'y' => 0,
	'w' => 0.75,
	'h' => 0.90,
	'format' => array(
	     "R"=>0,
	     "G"=>0,
	     "B"=>0,
	     "Surrounding"=>-200,
	     "Alpha"=>10
	),
	'background' => array(
	    "R"=>0,
	    "G"=>0,
	    "B"=>0,
	    "Surrounding"=>-200,
	    "Alpha"=>10
	),
	'scale' => array(
	    "DrawSubTicks"=>FALSE,
	    "Mode"=>SCALE_MODE_START0,
	    "DrawXLines" => FALSE,
	    #"DrawYLines" => FALSE,
	    'series' => array(
		'^hist_memusage$'
	    )
	),
	'abscissa' => 'hist_labels',
	'axis' => array(
	    0 => array(
		'name' => 'Node Count',
	    )
	),
	'series' => array(
	    'hist_labels' => array(
		'name' => '% of Nodes',
	    ),
 	    'hist_memusage' => array(
		'name' => 'Node Memory Usage',
		'palette' => array(
		    "R"=>0,
		    "G"=>100,
		    "B"=>255,
		    "Alpha"=>25
		)  
	    ),
	),
	'draw' => array(
 	    array(
		'type' => 'bar',
		'series' => array(
		    '^hist_memusage$'
		),
		'format' => array(
		    "DisplayValues"=>FALSE,
		    "DisplayColor"=>DISPLAY_AUTO,
		    "Surrounding"=>20
		)
	    )
	)
    ),
    array(
	'type' => 'legend',
	'x' => 0.87,
	'y' => 0.05, 
	'series' => array(
	    'hist_memusage'
	),
	'format' => array(
	    "Style" => LEGEND_ROUND,
	    "Mode" => LEGEND_VERTICAL,
	    "Family" => LEGEND_FAMILY_CIRCLE,
	    "FontSize" => 12,
	    "FontR" => 0,
	    "FontG" => 0,
	    "FontB" => 255,
	    "Alpha" => 50
	) 
    )
); 
 
$TEMPLATES['util_stack'] = array(
    array(
	'type' => 'graph',
	'x' => 0.1,
	'y' => 0,
	'w' => 0.75,
	'h' => 0.90,
	'format' => array(
	     "R"=>0,
	     "G"=>0,
	     "B"=>0,
	     "Surrounding"=>-200,
	     "Alpha"=>10
	),
	'background' => array(
	    "R"=>0,
	    "G"=>0,
	    "B"=>0,
	    "Surrounding"=>-200,
	    "Alpha"=>10
	),
	'scale' => array(
	    "DrawSubTicks"=>FALSE,
	    "Mode"=>SCALE_MODE_MANUAL,
	    "YMax" => 'YMax',
	    "YMin" => 'YMin',
	    "XMargin" => 0,
	    "YMargin" => 0,
	    "DrawXLines" => FALSE,
	    #"DrawYLines" => FALSE,
	    'series' => array(
		'^cpus$',
		'^maxcpus$'
	    )
	),
	'abscissa' => 'Labels',
	'axis' => array(
	    0 => array(
		'name' => 'CPUs',
	    )
	),
	'series' => array(
	    'Labels' => array(
		'name' => 'Date',
	    ),
 	    'maxcpus' => array(
		'name' => 'Max Cores',
	    ),
  	    'maxthreads' => array(
		'name' => 'Max Threads',
 		'palette' => array(
		    "R"=>255,
		    "G"=>50,
		    "B"=>0,
		    "Alpha"=>100
		)               
	    ),              
 	    'cpus' => array(
		'name' => 'Threads Scheduled',
		'palette' => array(
		    "R"=>153,
		    "G"=>255,
		    "B"=>153,
		    "Alpha"=>95
		) 
	    ),
  	    'actcpus' => array(
		'name' => 'Threads Available',
		'palette' => array(
		    "R"=>0,
		    "G"=>100,
		    "B"=>0,
		    "Alpha"=>15
		)  	    
	    ), 
   	    'usercpus' => array(
		'name' => 'User Thread Usage',
 		'palette' => array(
		    "R"=>0,
		    "G"=>204,
		    "B"=>255,
		    "Alpha"=>99
		)  	        
	    ),  
   	    'nicecpus' => array(
		'name' => 'Nice Thread usage',
  		'palette' => array(
		    "R"=>0,
		    "G"=>153,
		    "B"=>255,
		    "Alpha"=>99
		)  	       
	    ),  
    	    'wiocpus' => array(
		'name' => 'Wait I/O Thread Usage',
  		'palette' => array(
		    "R"=>0,
		    "G"=>102,
		    "B"=>255,
		    "Alpha"=>99
		)  	       
	    ),  
    	    'systemcpus' => array(
		'name' => 'System Thread Usage',
  		'palette' => array(
		    "R"=>153,
		    "G"=>153,
		    "B"=>255,
		    "Alpha"=>99
		)  	       
	    )
	),
	'draw' => array(
	    array(
		'type' => 'stack',
		'series' => array(
		    '^actcpus$'
		),
		'format' => array(
		    "DisplayValues"=>FALSE,
		    "DisplayColor"=>DISPLAY_AUTO,
		    "Surrounding"=>20
		)
	    ),                   
	    array(
		'type' => 'stack',
		'series' => array(
		    '^cpus$'
		),
		'format' => array(
		    "DisplayValues"=>FALSE,
		    "DisplayColor"=>DISPLAY_AUTO,
		    "Surrounding"=>20
		)
	    ),
            array(
		'type' => 'stack',
		'series' => array(
		    '^(user|nice|wio|system)cpus$'
		),
		'format' => array(
		    "DisplayValues"=>FALSE,
		    "DisplayColor"=>DISPLAY_AUTO,
		    "Surrounding"=>20
		)
	    ),                  
	    array(
		'type' => 'line',    
		'series' => array('^(maxcpus|maxthreads)$'),
 		'format' => array(
		    "Weight"=>5
		) 
	    )  
	)
    ),
    array(
	'type' => 'legend',
	'x' => 0.87,
	'y' => 0.05, 
	'series' => array(
	    'cpus|threads'
	),
	'format' => array(
	    "Style" => LEGEND_ROUND,
	    "Mode" => LEGEND_VERTICAL,
	    "Family" => LEGEND_FAMILY_CIRCLE,
	    "FontSize" => 12,
	    "FontR" => 0,
	    "FontG" => 0,
	    "FontB" => 255,
	    "Alpha" => 50
	) 
    )
); 

$TEMPLATES['util_percent_stack'] = $TEMPLATES['util_stack'];
$TEMPLATES['util_percent_stack'][0]['axis'][0]['name'] = '% CPU Threads';
 
$TEMPLATES['util_job_stack'] = array(
    array(
	'type' => 'graph',
	'x' => 0.1,
	'y' => 0,
	'w' => 0.69,
	'h' => 0.90,
	'format' => array(
	     "R"=>0,
	     "G"=>0,
	     "B"=>0,
	     "Surrounding"=>-200,
	     "Alpha"=>10
	),
	'background' => array(
	    "R"=>0,
	    "G"=>0,
	    "B"=>0,
	    "Surrounding"=>-200,
	    "Alpha"=>10
	),
	'scale' => array(
	    "DrawSubTicks"=>FALSE,
	    "Mode"=>SCALE_MODE_MANUAL,
	    "YMax" => 'YMax',
	    "YMin" => 'YMin',
	    "XMargin" => 0,
	    "YMargin" => 0,
	    "DrawXLines" => FALSE,
	    #"DrawYLines" => FALSE,
	    'series' => array(
		'^cpus$',
		'^maxcpus$'
	    )
	),
	'abscissa' => 'Labels',
	'axis' => array(
	    0 => array(
		'name' => 'CPUs',
	    )
	),
	'series' => array(
	    'Labels' => array(
		'name' => 'Date',
	    ),
 	    'maxcpus' => array(
		'name' => 'Max Batch Cores',
	    ),
 	    'cpus' => array(
		#'name' => 'CPUs Assigned',
		'palette' => array(
		    "R"=>0,
		    "G"=>100,
		    "B"=>255,
		    "Alpha"=>25
		) 
	    ),
  	    'exitnonzero' => array(
		#'name' => 'Exit Non Zero',
		'palette' => array(
		    "R"=>255,
		    "G"=>255,
		    "B"=>51,
		    "Alpha"=>100
		)  	    
	    ), 
  	    'failed' => array(
		#'name' => 'Failed',
		'palette' => array(
		    "R"=>255,
		    "G"=>20,
		    "B"=>0,
		    "Alpha"=>100
		)  	    
	    ), 
	),
	'draw' => array(
	    array(
		'type' => 'stack',
		'series' => array(
		    '^actcpus$'
		),
		'format' => array(
		    "DisplayValues"=>FALSE,
		    "DisplayColor"=>DISPLAY_AUTO,
		    "Surrounding"=>20
		)
	    ),                   
 	    array(
		'type' => 'stack',
		'series' => array(
		    '^cpus$'
		),
		'format' => array(
		    "DisplayValues"=>FALSE,
		    "DisplayColor"=>DISPLAY_AUTO,
		    "Surrounding"=>20
		)
	    ),                    
            array(
		'type' => 'stack',
		'series' => array(
		    '^(user|nice|wio|system)cpus$'
		),
		'format' => array(
		    "DisplayValues"=>FALSE,
		    "DisplayColor"=>DISPLAY_AUTO,
		    "Surrounding"=>20
		)
	    ),                  
	    array(
		'type' => 'line',    
		'series' => array('^maxcpus$','^exitnonzero$','^failed$'),
 		'format' => array(
		    "Weight"=>5
		) 
	    )  
	)
    ),
    array(
	'type' => 'div',
	'x' => 0.832,
	'y' => 0.12,
	'w' => 0.2,
	'h' => 0.5,
	'draw' => array( 
  	    array(
        	'type' => 'text',
        	'value' => 'Killed or Failed Jobs VS Total Jobs',
        	'x' => 0.3,
        	'y' => 0.2, 
        	'w' => 1.0,
        	'h' => 1.0, 	
        	'format' => array(
        	    "R"=>0,
        	    "G"=>0,
        	    "B"=>0,
        	    "Align"=>TEXT_ALIGN_TOPMIDDLE
        	)
            ),
	    array(
		'type' => '3dpie',
		'x' => 0.3,
		'y' => 0.5,
		'r' => 0.45,
		'abscissa' => 'jobfailpie',
		'values' => 'jobfailpievalues',
		'palette' => array(
		    array(
			"R"=>0,
			"G"=>100,
			"B"=>255,
			"Alpha"=>25
		    ),
		    array(
			"R"=>255,
			"G"=>20,
			"B"=>0,
			"Alpha"=>100
		    ) 
		),
		'format' => array(
		    "SecondPass"=>TRUE,
		    "DrawLabels"=>FALSE,
		    "WriteValues"=>TRUE,
		    "LabelStacked"=>TRUE,
		    "Border"=>TRUE,
		    "ValuePosition"=>"PIE_VALUE_INSIDE",
		    "DataGapAngle"=>0,
		    "LabelStacked"=>TRUE,
		    "LabelR"=>40,
		    "LabelG"=>0,
		    "LabelB"=>0,
		    "LabelAlpha"=>90,
		    "ValueR"=>0,
		    "ValueG"=>10,
		    "ValueB"=>100,
		    "ValueAlphaA"=>90
		)
	    )
	)
    ),
    array(
	'type' => 'div',
	'x' => 0.832,
	'y' => 0.40,
	'w' => 0.2,
	'h' => 0.5,
	'draw' => array( 
  	    array(
        	'type' => 'text',
        	'value' => 'NonZero Exit Jobs VS Total Jobs',
        	'x' => 0.3,
        	'y' => 0.2, 
        	'w' => 1.0,
        	'h' => 1.0, 	
        	'format' => array(
        	    "R"=>0,
        	    "G"=>0,
        	    "B"=>0,
        	    "Align"=>TEXT_ALIGN_TOPMIDDLE
        	)
            ),
	    array(
		'type' => '3dpie',
		'x' => 0.3,
		'y' => 0.5,
		'r' => 0.45,
		'abscissa' => 'jobNonZeropie',
		'values' => 'jobNonZeropievalues',
		'palette' => array(
		    array(
			"R"=>0,
			"G"=>100,
			"B"=>255,
			"Alpha"=>25
		    ),
 		    array(
			"R"=>255,
			"G"=>255,
			"B"=>51,
			"Alpha"=>100
		    ) 
		),
		'format' => array(
		    "SecondPass"=>TRUE,
		    "DrawLabels"=>FALSE,
		    "WriteValues"=>TRUE,
		    "LabelStacked"=>TRUE,
		    "Border"=>TRUE,
		    "ValuePosition"=>"PIE_VALUE_INSIDE",
		    "DataGapAngle"=>0,
		    "LabelStacked"=>TRUE,
		    "LabelR"=>40,
		    "LabelG"=>0,
		    "LabelB"=>0,
		    "LabelAlpha"=>90,
		    "ValueR"=>0,
		    "ValueG"=>10,
		    "ValueB"=>100,
		    "ValueAlphaA"=>90
		)
	    )
	)
    ), 
    array(
	'type' => 'legend',
	'x' => 0.81,
	'y' => 0.05, 
	'series' => array(
	    '^cpus$',
	    'failed',
	    'exitnonzero'
	),
	'format' => array(
	    "Style" => LEGEND_ROUND,
	    "Mode" => LEGEND_VERTICAL,
	    "Family" => LEGEND_FAMILY_CIRCLE,
	    "FontSize" => 12,
	    "FontR" => 0,
	    "FontG" => 0,
	    "FontB" => 255,
	    "Alpha" => 50
	) 
    )
); 

$TEMPLATES['job_fail_pie'] = array(
    array(
	'type' => 'div',
	'x' => 0.1,
	'y' => 0.2,
	'w' => 0.25,
	'h' => 0.5,
	'draw' => array( 
  	    array(
        	'type' => 'text',
        	'value' => "Jobs Exit Status",
        	'x' => 0.5,
        	'y' => 0.0, 
        	'w' => 1.0,
        	'h' => 1.0, 	
        	'format' => array(
        	    "R"=>0,
        	    "G"=>0,
        	    "B"=>0,
        	    "Align"=>TEXT_ALIGN_TOPMIDDLE
        	)
            ),
	    array(
		'type' => '3dpie',
		'x' => 0.5,
		'y' => 0.32,
		'r' => 0.4,
		'abscissa' => 'jobfailpie',
		'values' => 'jobfailpievalues',
		'palette' => array(
		    array(
			"R"=>0,
			"G"=>100,
			"B"=>0,
			"Alpha"=>25
		    ),
		    array(
			"R"=>255,
			"G"=>20,
			"B"=>0,
			"Alpha"=>100
		    ),
 		    array(
			"R"=>255,
			"G"=>255,
			"B"=>51,
			"Alpha"=>100
		    )         
		),
		'format' => array(
		    "SecondPass"=>TRUE,
		    "DrawLabels"=>FALSE,
		    "WriteValues"=>FALSE,
		    "LabelStacked"=>TRUE,
		    "Border"=>TRUE,
		    "ValuePosition"=>"PIE_VALUE_OUTSIDE",
		    "DataGapAngle"=>0,
		    "LabelStacked"=>TRUE,
		    "LabelR"=>40,
		    "LabelG"=>0,
		    "LabelB"=>0,
		    "LabelAlpha"=>90,
		    "ValueR"=>0,
		    "ValueG"=>10,
		    "ValueB"=>100,
		    "ValueAlphaA"=>90
		),
		'legend' => array(
		    'x' => 0.0,
		    'y' => 0.6, 
		    'format' => array(
			"Style" => LEGEND_ROUND,
			"Mode" => LEGEND_VERTICAL,
			"Family" => LEGEND_FAMILY_CIRCLE,
			"FontSize" => 12,
			"FontR" => 0,
			"FontG" => 0,
			"FontB" => 255,
			"Alpha" => 50
		    ) 
		) 
	    )
	)
    ),
    array(
	'type' => 'div',
	'x' => 0.2,
	'y' => 0.0,
	'w' => 1,
	'h' => 0.5,
	'draw' => array( 
  	    array(
        	'type' => 'text',
        	'content' => 'jobfaillabel_nzlsf',
        	'x' => 0.5,
        	'y' => 0.0, 
        	'w' => 1.0,
        	'h' => 1.0, 	
        	'format' => array(
        	    "R"=>0,
        	    "G"=>0,
        	    "B"=>0,
        	    "Align"=>TEXT_ALIGN_TOPMIDDLE
        	)
            ),
	    array(
		'type' => '3dpie',
		'x' => 0.5,
		'y' => 0.6,
		'r' => 0.35,
		'abscissa' => 'exitcodes_known',
		'values' => 'exitcodesnonzerocount_known',
		'palette' => array(
		    array(
			"R"=>0,
			"G"=>100,
			"B"=>255,
			"Alpha"=>25
		    ),
		    array(
			"R"=>255,
			"G"=>20,
			"B"=>0,
			"Alpha"=>100
		    ) 
		),
		'format' => array(
		    "SecondPass"=>TRUE,
		    "DrawLabels"=>TRUE,
		    "WriteValues"=>TRUE,
		    "LabelStacked"=>TRUE,
		    "Border"=>TRUE,
		    "ValuePosition"=>"PIE_VALUE_INSIDE",
		    "DataGapAngle"=>0,
		    "LabelStacked"=>TRUE,
		    "LabelR"=>40,
		    "LabelG"=>0,
		    "LabelB"=>0,
		    "LabelAlpha"=>90,
		    "ValueR"=>0,
		    "ValueG"=>10,
		    "ValueB"=>100,
		    "ValueAlphaA"=>90
		)
	    )
	)
    ),
    array(
	'type' => 'div',
	'x' => 0.2,
	'y' => 0.5,
	'w' => 1,
	'h' => 0.5,
	'draw' => array( 
  	    array(
        	'type' => 'text',
        	'content' => 'jobfaillabel_nzuser',
        	'x' => 0.5,
        	'y' => 0.0, 
        	'w' => 1.0,
        	'h' => 1.0, 	
        	'format' => array(
        	    "R"=>0,
        	    "G"=>0,
        	    "B"=>0,
        	    "Align"=>TEXT_ALIGN_TOPMIDDLE
        	)
            ),
	    array(
		'type' => '3dpie',
		'x' => 0.5,
		'y' => 0.6,
		'r' => 0.35,
		'abscissa' => 'jexitcodes_culled',
		'values' => 'jexitcodesfailcount_culled',
		'palette' => array(
		    array(
			"R"=>0,
			"G"=>100,
			"B"=>255,
			"Alpha"=>25
		    ),
		    array(
			"R"=>255,
			"G"=>20,
			"B"=>0,
			"Alpha"=>100
		    ) 
		),
		'format' => array(
		    "SecondPass"=>TRUE,
		    "DrawLabels"=>TRUE,
		    "WriteValues"=>TRUE,
		    "LabelStacked"=>TRUE,
		    "Border"=>TRUE,
		    "ValuePosition"=>"PIE_VALUE_INSIDE",
		    "DataGapAngle"=>0,
		    "LabelStacked"=>TRUE,
		    "LabelR"=>40,
		    "LabelG"=>0,
		    "LabelB"=>0,
		    "LabelAlpha"=>90,
		    "ValueR"=>0,
		    "ValueG"=>10,
		    "ValueB"=>100,
		    "ValueAlphaA"=>90
		)
	    )
	)
    )
); 
 

$TEMPLATES['gpu_stack'] = array(
    array(
	'type' => 'graph',
	'x' => 0.1,
	'y' => 0,
	'w' => 0.75,
	'h' => 0.90,
	'format' => array(
	     "R"=>0,
	     "G"=>0,
	     "B"=>0,
	     "Surrounding"=>-200,
	     "Alpha"=>10
	),
	'background' => array(
	    "R"=>0,
	    "G"=>0,
	    "B"=>0,
	    "Surrounding"=>-200,
	    "Alpha"=>10
	),
        'scale' => array(
	    "DrawSubTicks"=>FALSE,
	    "Mode"=>SCALE_MODE_ADDALL_START0,
	    "DrawXLines" => FALSE,
	    #"DrawYLines" => FALSE
	    'series' => array(
		'^gpu_util_'
	    ) 
	),
	'abscissa' => 'labels',
	'axis' => array(
	    0 => array(
		'name' => '% Percent',
	    )
	),
	'series' => array(
	    'Labels' => array(
		'name' => 'Date',
	    ),
    	    'sflops' => array(
		'name' => 'Single FLOPS',
	    ),
     	    'dflops' => array(
		'name' => 'Double FLOPS',
	    )
	),
	'draw' => array(
 	    array(
		'type' => 'stack',
		'series' => array(
		    '^gpu_'
		),
		'format' => array(
		    "DisplayValues"=>FALSE,
		    "DisplayColor"=>DISPLAY_AUTO,
		    "Surrounding"=>20
		)
	    )
	)
    ),
    array(
	'type' => 'legend',
	'x' => 0.87,
	'y' => 0.05, 
	'series' => array(
	    '^gpu_util_'
	),
	'format' => array(
	    "Style" => LEGEND_ROUND,
	    "Mode" => LEGEND_VERTICAL,
	    "Family" => LEGEND_FAMILY_CIRCLE,
	    "FontSize" => 12,
	    "FontR" => 0,
	    "FontG" => 0,
	    "FontB" => 255,
	    "Alpha" => 50
	) 
    )
);

$TEMPLATES['flop_stack'] = array(
    array(
	'type' => 'graph',
	'x' => 0.1,
	'y' => 0,
	'w' => 0.75,
	'h' => 0.90,
	'format' => array(
	     "R"=>0,
	     "G"=>0,
	     "B"=>0,
	     "Surrounding"=>-200,
	     "Alpha"=>10
	),
	'background' => array(
	    "R"=>0,
	    "G"=>0,
	    "B"=>0,
	    "Surrounding"=>-200,
	    "Alpha"=>10
	),
        'scale' => array(
	    "DrawSubTicks"=>FALSE,
	    "Mode"=>SCALE_MODE_ADDALL_START0,
	    "DrawXLines" => FALSE,
	    #"DrawYLines" => FALSE
	    'series' => array(
		'[sd]flops|flops'
	    ) 
	),
	'abscissa' => 'labels',
	'axis' => array(
	    0 => array(
		'name' => 'TFLOPS',
	    )
	),
	'series' => array(
	    'Labels' => array(
		'name' => 'Date',
	    ),
    	    'sflops' => array(
		'name' => 'Single FLOPS',
	    ),
     	    'dflops' => array(
		'name' => 'Double FLOPS',
	    ), 
     	    'flops' => array(
		'name' => 'FLOPS',
	    )
	),
	'draw' => array(
 	    array(
		'type' => 'stack',
		'series' => array(
		    '^flops$',
		    '^dflops$',
		    '^sflops$'
		),
		'format' => array(
		    "DisplayValues"=>FALSE,
		    "DisplayColor"=>DISPLAY_AUTO,
		    "Surrounding"=>20
		)
	    )
	)
    ),
    array(
	'type' => 'legend',
	'x' => 0.87,
	'y' => 0.05, 
	'series' => array(
	    'flops',
	    'dflops',
	    'sflops'
	),
	'format' => array(
	    "Style" => LEGEND_ROUND,
	    "Mode" => LEGEND_VERTICAL,
	    "Family" => LEGEND_FAMILY_CIRCLE,
	    "FontSize" => 12,
	    "FontR" => 0,
	    "FontG" => 0,
	    "FontB" => 255,
	    "Alpha" => 50
	) 
    )
); 

$TEMPLATES['sch_node_stack'] = array(
    0 => array(
	'type' => 'graph',
	'x' => 0.1,
	'y' => 0,
	'w' => 0.75,
	'h' => 0.90,
	'format' => array(
	     "R"=>0,
	     "G"=>0,
	     "B"=>0,
	     "Surrounding"=>-200,
	     "Alpha"=>10
	),
	'background' => array(
	    "R"=>0,
	    "G"=>0,
	    "B"=>0,
	    "Surrounding"=>-200,
	    "Alpha"=>10
	),
        'scale' => array(
	    "DrawSubTicks"=>FALSE,
	    "Mode"=>SCALE_MODE_ADDALL_START0,
	    "DrawXLines" => FALSE,
	    #"DrawYLines" => FALSE
	    'series' => array(
		'^bnodecount'
	    ) 
	),
	'abscissa' => 'labels',
	'axis' => array(
	    0 => array(
		'name' => 'Nodes',
	    )
	),
	'series' => array(
	    'Labels' => array(
		'name' => 'Date',
	    ),   
    	    'bnodecount' => array(
		'name' => 'Node Count',
	    ),
	    'bnodeok' => array(
		'name' => 'Nodes Ok',
	    ), 
    	    'bnodeadmindown' => array(
		'name' => 'Nodes AdminDown',
	    ),                                
    	    'bnodeunreach' => array(
		'name' => 'Nodes Unreachable',
	    ),                               
    	    'bnodeunavail' => array(
		'name' => 'Nodes Unavailable',
	    ),                               
     	    'bnodelimunavail' => array(
		'name' => 'Nodes Lim Unavailable',
	    ),                                
    	    'bnodelimclosed' => array(
		'name' => 'Nodes Lim Closed',
	    ),
    	    'bnodelimlocked' => array(
		'name' => 'Nodes Lim Locked',
	    ),
	),
	'draw' => array(
 	    array(
		'type' => 'stack',
		'series' => array(
		    '^bnodecount$'
		),
		'format' => array(
		    "DisplayValues"=>FALSE,
		    "DisplayColor"=>DISPLAY_AUTO,
		    "Surrounding"=>20
		)
	    ),
	    array(
		'type' => 'stack',
		'series' => array(
		    '^bnodeok$',
		    '^bnodeadmindown$',
		    '^bnodelimclosed$',
		    '^bnodelimlocked$'
		),
		'format' => array(
		    "DisplayValues"=>FALSE,
		    "DisplayColor"=>DISPLAY_AUTO,
		    "Surrounding"=>20
		)
	    ),
	)
    ),
    array(
	'type' => 'legend',
	'x' => 0.87,
	'y' => 0.05, 
	'series' => array(
	    'bnode'
	),
	'format' => array(
	    "Style" => LEGEND_ROUND,
	    "Mode" => LEGEND_VERTICAL,
	    "Family" => LEGEND_FAMILY_CIRCLE,
	    "FontSize" => 12,
	    "FontR" => 0,
	    "FontG" => 0,
	    "FontB" => 255,
	    "Alpha" => 50
	) 
    )
); 

$TEMPLATES['sch_node_percent_stack'] = $TEMPLATES['sch_node_stack'];
$TEMPLATES['sch_node_percent_stack'][0]['axis'][0]['name'] = '% Nodes';
$TEMPLATES['sch_node_percent_stack'][0]['scale'] = array(
    "DrawSubTicks"=>FALSE,
    "Mode"=>SCALE_MODE_MANUAL,
    "YMax" => 'YMax',
    "YMin" => 'YMin',
    "XMargin" => 0,
    "YMargin" => 0,
    "DrawXLines" => FALSE,
    'series' => array(
	'^bnodecount' 
    )
);

$TEMPLATES['util_sch_node_stack'] = array(
    0 => array(
	'type' => 'graph',
	'x' => 0.1,
	'y' => 0,
	'w' => 0.75,
	'h' => 0.90,
	'format' => array(
	     "R"=>0,
	     "G"=>0,
	     "B"=>0,
	     "Surrounding"=>-200,
	     "Alpha"=>10
	),
	'background' => array(
	    "R"=>0,
	    "G"=>0,
	    "B"=>0,
	    "Surrounding"=>-200,
	    "Alpha"=>10
	),
        'scale' => array(
	    "DrawSubTicks"=>FALSE,
	    "Mode"=>SCALE_MODE_ADDALL_START0,
	    "DrawXLines" => FALSE,
	    #"DrawYLines" => FALSE
	    'series' => array(
		'^bnodecount'
	    ) 
	),
	'abscissa' => 'labels',
	'axis' => array(
	    0 => array(
		'name' => 'Nodes',
	    )
	),
	'series' => array(
	    'Labels' => array(
		'name' => 'Date',
	    ),   
    	    'bnodecount' => array(
		'name' => 'Node Count',
		'palette' => array(
		    "R"=>0,
		    "G"=>80,
		    "B"=>80,
		    "Alpha"=>15
		) 
	    ),
	    'bnodeok' => array(
		'name' => 'Nodes Ok',   
		'palette' => array(
		    "R"=>0,
		    "G"=>255,
		    "B"=>80,
		    "Alpha"=>98
		)          
	    ), 
    	    'bnoderun' => array(
		'name' => 'Nodes Assigned Job',
		'palette' => array(
		    "R"=>0,
		    "G"=>140,
		    "B"=>140,
		    "Alpha"=>98
		)         
	    )
	),
	'draw' => array(
 	    array(
		'type' => 'stack',
		'series' => array(
		    '^bnodecount$'
		),
		'format' => array(
		    "DisplayValues"=>FALSE,
		    "DisplayColor"=>DISPLAY_AUTO,
		    "Surrounding"=>20
		)
	    ),
	    array(
		'type' => 'stack',
		'series' => array(
		    '^bnodeok$'
		),
		'format' => array(
		    "DisplayValues"=>FALSE,
		    "DisplayColor"=>DISPLAY_AUTO,
		    "Surrounding"=>20
		)
	    ),                  
	    array(
		'type' => 'stack',
		'series' => array(
		    '^bnoderun$'
		),
		'format' => array(
		    "DisplayValues"=>FALSE,
		    "DisplayColor"=>DISPLAY_AUTO,
		    "Surrounding"=>20
		)
	    )
	)
    ),
    array(
	'type' => 'legend',
	'x' => 0.87,
	'y' => 0.05, 
	'series' => array(
	    'bnodecount',
	    'bnoderun',
	    'bnodeok'
	),
	'format' => array(
	    "Style" => LEGEND_ROUND,
	    "Mode" => LEGEND_VERTICAL,
	    "Family" => LEGEND_FAMILY_CIRCLE,
	    "FontSize" => 12,
	    "FontR" => 0,
	    "FontG" => 0,
	    "FontB" => 255,
	    "Alpha" => 50
	) 
    )
);

$TEMPLATES['util_sch_node_percent_stack'] = $TEMPLATES['util_sch_node_stack'];
$TEMPLATES['util_sch_node_percent_stack'][0]['axis'][0]['name'] = '% Nodes';
$TEMPLATES['util_sch_node_percent_stack'][0]['scale'] = array(
    "DrawSubTicks"=>FALSE,
    "Mode"=>SCALE_MODE_MANUAL,
    "YMax" => 'YMax',
    "YMin" => 'YMin',
    "XMargin" => 0,
    "YMargin" => 0,
    "DrawXLines" => FALSE,
    'series' => array(
	'^bnodecount' 
    )
); 

$TEMPLATES['lsf_avail_pie'] = array(
    array(
	'type' => 'div',
	'x' => 0.53,
	'y' => 0.5,
	'w' => 0.4,
	'h' => 0.3,
	'draw' => array( 
	    array(
		'type' => 'text',
		'content' => 'maxuptime',
		'x' => 0.5,
		'y' => 0.0, 
		'w' => 0.99,
		'h' => 0.1, 	
		'format' => array(
		    "R"=>0,
		    "G"=>0,
		    "B"=>0,
		    "Align"=>TEXT_ALIGN_TOPMIDDLE
		)
	    ),             
	    array(
		'type' => 'text',
		'content' => 'uptime',
		'x' => 0.5,
		'y' => 0.1, 
		'w' => 0.99,
		'h' => 0.1, 	
		'format' => array(
		    "R"=>0,
		    "G"=>0,
		    "B"=>0,
		    "Align"=>TEXT_ALIGN_TOPMIDDLE
		)
	    ),
 	    array(
		'type' => 'text',
		'content' => 'unknowntime',
		'x' => 0.5,
		'y' => 0.2, 
		'w' => 0.99,
		'h' => 0.1, 	
		'format' => array(
		    "R"=>0,
		    "G"=>0,
		    "B"=>0,
		    "Align"=>TEXT_ALIGN_TOPMIDDLE
		)
	    ), 
  	    array(
		'type' => 'text',
		'content' => 'maxcputime',
		'x' => 0.5,
		'y' => 0.3, 
		'w' => 0.99,
		'h' => 0.1, 	
		'format' => array(
		    "R"=>0,
		    "G"=>0,
		    "B"=>0,
		    "Align"=>TEXT_ALIGN_TOPMIDDLE
		)
	    ),
 	    array(
		'type' => 'text',
		'content' => 'utiltime',
		'x' => 0.5,
		'y' => 0.4, 
		'w' => 0.99,
		'h' => 0.1, 	
		'format' => array(
		    "R"=>0,
		    "G"=>0,
		    "B"=>0,
		    "Align"=>TEXT_ALIGN_TOPMIDDLE
		)
	    )
	)
    ),
    array(
	'type' => 'div',
	'x' => 0.13,
	'y' => 0.45,
	'w' => 0.3,
	'h' => 0.5,
	'draw' => array( 
  	    array(
		'type' => 'text',
		'value' => 'Node Utilization',
		'x' => 0.5,
		'y' => 0.0, 
		'w' => 1.0,
		'h' => 1.0, 	
		'format' => array(
		    "R"=>0,
		    "G"=>0,
		    "B"=>0,
		    "Align"=>TEXT_ALIGN_TOPMIDDLE
		)
	    ),
	    array(
		'type' => '3dpie',
		'x' => 0.5,
		'y' => 0.5,
		'r' => 0.45,
		'abscissa' => 'schcpu',
		'values' => 'schcputimes',
		'series' => array(
		    'UserName' => array(
			'name' => 'User Name',
		    ),
		    'CPUhr' => array(
			'name' => 'Number of CPU Hours',
		    )
		), 
		'format' => array(
		    "SecondPass"=>TRUE,
		    "DrawLabels"=>TRUE,
		    "WriteValues"=>FALSE,
		    "LabelStacked"=>TRUE,
		    "Border"=>TRUE,
		    "ValuePosition"=>"PIE_VALUE_INSIDE",
		    "DataGapAngle"=>0,
		    "LabelStacked"=>TRUE,
		    "LabelR"=>40,
		    "LabelG"=>0,
		    "LabelB"=>0,
		    "LabelAlpha"=>90,
		    "ValueR"=>0,
		    "ValueG"=>10,
		    "ValueB"=>100,
		    "ValueAlphaA"=>90
		)
	    )
	)
    ),
    array(
	'type' => 'div',
	'x' => 0.13,
	'y' => 0.0,
	'w' => 0.3,
	'h' => 0.5,
	'draw' => array( 
  	    array(
		'type' => 'text',
		'value' => 'Node States',
		'x' => 0.5,
		'y' => 0.0, 
		'w' => 1.0,
		'h' => 1.0, 	
		'format' => array(
		    "R"=>0,
		    "G"=>0,
		    "B"=>0,
		    "Align"=>TEXT_ALIGN_TOPMIDDLE
		)
	    ),
	    array(
		'type' => '3dpie',
		'x' => 0.5,
		'y' => 0.5,
		'r' => 0.45,
		'abscissa' => 'nodestates',
		'values' => 'nodestatetimes',
		'series' => array(
		    'UserName' => array(
			'name' => 'User Name',
		    ),
		    'CPUhr' => array(
			'name' => 'Number of CPU Hours',
		    )
		), 
		'format' => array(
		    "SecondPass"=>TRUE,
		    "DrawLabels"=>TRUE,
		    "WriteValues"=>FALSE,
		    "LabelStacked"=>TRUE,
		    "Border"=>TRUE,
		    "ValuePosition"=>"PIE_VALUE_INSIDE",
		    "DataGapAngle"=>0,
		    "LabelStacked"=>TRUE,
		    "LabelR"=>40,
		    "LabelG"=>0,
		    "LabelB"=>0,
		    "LabelAlpha"=>90,
		    "ValueR"=>0,
		    "ValueG"=>10,
		    "ValueB"=>100,
		    "ValueAlphaA"=>90
		)
	    )
	)
    ),
    array(
	'type' => 'div',
	'x' => 0.6,
	'y' => 0.0,
	'w' => 0.3,
	'h' => 0.5,
	'draw' => array(  
  	    array(
		'type' => 'text',
		'value' => 'Node Status',
		'x' => 0.5,
		'y' => 0.0, 
		'w' => 1.0,
		'h' => 1.0, 	
		'format' => array(
		    "R"=>0,
		    "G"=>0,
		    "B"=>0,
		    "Align"=>TEXT_ALIGN_TOPMIDDLE
		)
	    ),    
	    array(
		'type' => '3dpie',
		'x' => 0.5,
		'y' => 0.5,
		'r' => 0.45,
		'abscissa' => 'nodeupdown',
		'values' => 'nodeupdowntimes',
		'series' => array(
		    'UserName' => array(
			'name' => 'User Name',
		    ),
		    'CPUhr' => array(
			'name' => 'Number of CPU Hours',
		    )
		), 
		'format' => array(
		    "SecondPass"=>TRUE,
		    "DrawLabels"=>TRUE,
		    "WriteValues"=>FALSE,
		    "LabelStacked"=>TRUE,
		    "Border"=>TRUE,
		    "ValuePosition"=>"PIE_VALUE_INSIDE",
		    "DataGapAngle"=>0,
		    "LabelStacked"=>TRUE,
		    "LabelR"=>40,
		    "LabelG"=>0,
		    "LabelB"=>0,
		    "LabelAlpha"=>90,
		    "ValueR"=>0,
		    "ValueG"=>10,
		    "ValueB"=>100,
		    "ValueAlphaA"=>90
		)
	    )
	)
    ) 
);


$TEMPLATES['gauss_explain'] = array(
   0 => array(
	'type' => 'div',
	'x' => 0.07,
	'y' => 0.0,
	'w' => 1.0-0.07,
	'h' => 0.95,
	'draw' => array(
	    0 => array(
		'type' => 'shadow',
		'active' => TRUE
		),
	    1 => array(
		'type' => 'graph',
		'x' => 0,
		'y' => 0,
		'w' => 0.92,
		'h' => 0.85,
		'format' => array(
		     "R"=>0,
		     "G"=>0,
		     "B"=>0,
		     "Surrounding"=>-200,
		     "Alpha"=>10
		),
		'background' => array(
		    "R"=>0,
		    "G"=>0,
		    "B"=>0,
		    "Surrounding"=>-200,
		    "Alpha"=>10
		),
		'scale' => array(
		    "DrawSubTicks"=>FALSE,
		    "Mode"=>SCALE_MODE_START0,
		    "DrawXLines" => FALSE,
		    #"DrawYLines" => FALSE
		    'series' => array(
			'^y$'
		    ) 
		),
		'abscissa' => 'Labels',
		'axis' => array(
		    0 => array(
			#'units' => '',
			'name' => 'Value',
		    )
		),
		'series' => array(
		    'y' => array(
			'name' => 'Node Values',
			'palette' => array(
			    "R"=>0,
			    "G"=>0,
			    "B"=>0
			)
		    ),
 		    'ny' => array(
			'name' => 'Nonexistant Values',
			'palette' => array(
			    "R"=>255,
			    "G"=>0,
			    "B"=>0
			)
		    ) 
		),
		'draw' => array(
		    0 => array(
			'type' => 'zone',    
			'high' => 'b',
			'low' => 'z',
			'format' => array(
			    'LineR' => 0,
			    'LineG' => 0,
			    'LineB' => 255,
			    'LineAlpha' => 10,
			    #'LineTicks' => 10,
			    'AreaR' => 200,
			    'AreaG' => 200,
			    'AreaB' => 255,
			    'AreaAlpha' => 35
			)
		    ),
		    1 => array(
			'type' => 'zone',    
			'high' => 'd1',
			'low' => 'z',
			'format' => array(
			    'LineR' => 255,
			    'LineG' => 255,
			    'LineB' => 255,
			    'LineAlpha' => 90,
			    'LineTicks' => 2,
			    'AreaR' => 255,
			    'AreaG' => 255,
			    'AreaB' => 255,
			    'AreaAlpha' => 55
			)
		    ),
		    2 => array(
			'type' => 'line',    
			'series' => array('y', 'ny')
		    ) 
		)
	    )
	)
    ),
    1 => array(
	'type' => 'font',
	'format' => array(
	    #'FontName' => PCHART_DIR."/fonts/pf_arma_five.ttf",
	    'FontName' => 'pf_arma_five',
	    'FondSize' => 6
	)
    ),
    2 => array(
	'type' => 'legend',
	'x' => 0.50,
	'y' => 0.95, 
	'series' => array(
	    'y'
	),
	'format' => array(
	    "Style" => LEGEND_ROUND,
	    "Mode" => LEGEND_HORIZONTAL,
	    "Family" => LEGEND_FAMILY_CIRCLE,
	    "FontSize" => 12,
	    "FontR" => 0,
	    "FontG" => 0,
	    "FontB" => 255,
	    "Alpha" => 50
	)
    )
); 

$TEMPLATES['bw_lines'] = array(
   0 => array(
	'type' => 'div',
	'x' => 0.08,
	'y' => 0.0,
	'w' => 1.0-0.13,
	'h' => 0.97,
	'draw' => array(
	    0 => array(
		'type' => 'shadow',
		'active' => TRUE
		),
	    1 => array(
		'type' => 'graph',
		'x' => 0,
		'y' => 0,
		'w' => 0.92,
		'h' => 0.85,
		'format' => array(
		     "R"=>0,
		     "G"=>0,
		     "B"=>0,
		     "Surrounding"=>-200,
		     "Alpha"=>10
		),
		'background' => array(
		    "R"=>0,
		    "G"=>0,
		    "B"=>0,
		    "Surrounding"=>-200,
		    "Alpha"=>10
		),
		'scale' => array(
		    "DrawSubTicks"=>FALSE,
		    "Mode"=>SCALE_MODE_START0,
		    "DrawXLines" => FALSE,
		    #"DrawYLines" => FALSE
		    'series' => array(
			'^node_*'
		    ) 
		),
		'abscissa' => 'timestamps',
		'axis' => array(
		    0 => array(
			'units' => 'MB/s',
			'name' => 'Bandwidth',
		    )
		),
		'series' => array(
		    'avg' => array(
			'name' => 'Average CPU Load',
			'palette' => array(
			    "R"=>0,
			    "G"=>100,
			    "B"=>255
			)
		    )
		),
		'draw' => array(
		    array(
			'type' => 'line',    
			'series' => array('^node_*')
		    ) 
		)
	    )
	)
    ),
    1 => array(
	'type' => 'font',
	'format' => array(
	    #'FontName' => PCHART_DIR."/fonts/pf_arma_five.ttf",
	    'FontName' => 'pf_arma_five',
	    'FondSize' => 6
	)
    ),
    2 => array(
	'type' => 'legend',
	'x' => 0.89,
	'y' => 0.05, 
	'series' => array(
	    '^node_*'
	),
	'format' => array(
	    "Style" => LEGEND_ROUND,
	    "Mode" => LEGEND_VERTICAL,
	    "Family" => LEGEND_FAMILY_CIRCLE,
	    "FontSize" => 12,
	    "FontR" => 0,
	    "FontG" => 0,
	    "FontB" => 255,
	    "Alpha" => 50
	)
    ) 
); 

$TEMPLATES['util_rsv_hist'] = array(
    array(
	'type' => 'graph',
	'x' => 0.1,
	'y' => 0,
	'w' => 0.74,
	'h' => 0.83,
	'format' => array(
	     "R"=>0,
	     "G"=>0,
	     "B"=>0,
	     "Surrounding"=>-200,
	     "Alpha"=>10
	),
	'background' => array(
	    "R"=>0,
	    "G"=>0,
	    "B"=>0,
	    "Surrounding"=>-200,
	    "Alpha"=>10
	),
	'scale' => array(
	    "LabelRotation" => 90,
	    "DrawSubTicks"=>FALSE,
	    "Mode"=>SCALE_MODE_ADDALL_START0,
	    "DrawXLines" => FALSE,
	    #"DrawYLines" => FALSE,
	    'series' => array(
		'^usr_rsv$',
		'^sys_rsv$'
	    )
	),
	'abscissa' => 'Labels',
	'axis' => array(
	    0 => array(
		'name' => 'Reservation %',
	    )
	),
	'series' => array(
	    'Labels' => array(
		'name' => 'Reservation %',
	    ),
	    'usr_rsv' => array(
		'name' => 'User Reservation %',
	    ),
	    'sys_rsv' => array(
		'name' => 'System Reservation %',
	    )
	),
	'draw' => array(
	    array(
		'type' => 'line',
		'series' => array(
		    '^usr_rsv$',
		    '^sys_rsv$'
		),
		'format' => array(
		    "DisplayValues"=>FALSE,
		    "DisplayColor"=>DISPLAY_AUTO,
		    "Surrounding"=>20
		)
	    )
	)
    ),
    array(
	'type' => 'legend',
	'x' => 0.85,
	'y' => 0.05, 
	'series' => array(
	    '^usr_rsv$',
	    '^sys_rsv$'
	),
	'format' => array(
	    "Style" => LEGEND_ROUND,
	    "Mode" => LEGEND_VERTICAL,
	    "Family" => LEGEND_FAMILY_CIRCLE,
	    "FontSize" => 12,
	    "FontR" => 0,
	    "FontG" => 0,
	    "FontB" => 255,
	    "Alpha" => 50
	) 
    )
); 


  
 
?>
