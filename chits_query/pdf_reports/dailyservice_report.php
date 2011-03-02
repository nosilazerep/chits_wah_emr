<?php
    ob_start();
    
    require('./fpdf/fpdf.php');
    
    class PDF extends FPDF{
	var $widths;
	var $aligns;
	var $page;
	
	
	function SetWidths($w){
	    //Set the array of column widths
	    $this->widths=$w;
	}

	function SetAligns($a){
	    //Set the array of column alignments
	    $this->aligns=$a;
	}

	
	function Row($data){
	    //Calculate the height of the row
	    $nb=0;
	    for($i=0;$i<count($data);$i++)
	        $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
	    $h=5*$nb;
	    //Issue a page break first if needed
	    $this->CheckPageBreak($h);
	    //Draw the cells of the row
	    for($i=0;$i<count($data);$i++){
	        $w=$this->widths[$i];
	        $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'C'; //sets the alignment of text inside the cell
        	//Save the current position
	        $x=$this->GetX();
	        $y=$this->GetY();
        	//Draw the border
	        $this->Rect($x,$y,$w,$h);
        	//Print the text
	        $this->MultiCell($w,5,$data[$i],0,$a);
	        //Put the position to the right of the cell
	        $this->SetXY($x+$w,$y);
	    }

	    //Go to the next line
	    $this->Ln($h);
	}


	function CheckPageBreak($h){
	    //If the height h would cause an overflow, add a new page immediately
	    if($this->GetY()+$h>$this->PageBreakTrigger)
	        $this->AddPage($this->CurOrientation);
	}


	function NbLines($w,$txt)
	{
	    //Computes the number of lines a MultiCell of width w will take
	    $cw=&$this->CurrentFont['cw'];
	    if($w==0)
	        $w=$this->w-$this->rMargin-$this->x;
		$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
   	        $s=str_replace("\r",'',$txt);
	        $nb=strlen($s);
	    if($nb>0 and $s[$nb-1]=="\n")
        	$nb--;
		$sep=-1;
    		$i=0;
    		$j=0;
    		$l=0;
    		$nl=1;
    
		while($i<$nb){
		        $c=$s[$i];
	        if($c=="\n"){
	            $i++;
	            $sep=-1;
	            $j=$i;
	            $l=0;
	            $nl++;
	            continue;
	        }

	        if($c==' ')
	            $sep=$i;
        	    $l+=$cw[$c];
	        if($l>$wmax){
	            if($sep==-1){
                if($i==$j)
                    $i++;
            	}
            else
                $i=$sep+1;
	        $sep=-1;
            	$j=$i;
            	$l=0;
            	$nl++;
	    }
	    else
	    	$i++;
	    }
	    return $nl;
	}
      
      function Header(){
          $municipality_label = $_SESSION[datanode][name];
          
          $this->SetFont('Arial','B','10');
          $this->Cell(0,5,'D A I L Y   S E R V I C E    R E P O R T ( '.$_SESSION["subtitle"].' ) - '.$municipality_label,0,1,'C');
          
          if($_SESSION[report_date]==$_SESSION[end_report_date]):
              $this->Cell(0,5,$_SESSION[report_date],0,1,'C');
          else:
              $this->Cell(0,5,$_SESSION[report_date].' to '.$_SESSION[end_report_date],0,1,'C');          
          endif;
        
        $this->Cell(0,5,'Total Number of Records: '.$_SESSION[record_count],0,1,'L');          
        
        $this->SetFont('Arial','',11);        
        $w = $_SESSION["col_width"];
	$this->SetWidths($w);
	$this->Row($_SESSION[tbl_header]);		
      }    
      
      
      function ShowTable($header,$contents){
        $w = $_SESSION["col_width"];
      
	//$this->SetWidths($w);
	//$this->Row($header);
        //$this->Ln();
        $this->SetFont('Arial','',7);
	foreach($contents as $key=>$value){
	    foreach($value as $key2=>$value2){
	       $this->SetWidths($w);
	       $this->Row($value2);
	    }
	}      
      }
      

      function Footer(){
        $this->SetY(-15);
        //Arial italic 8
        $this->SetFont('Arial','I',8);
        //Page number
        $this->Cell(0,10,$this->PageNo(),0,0,'C');
      }
                                  
    }
        
    $pdf=new PDF('L','mm','Legal');
    //Column titles
    //Data loading

    switch($_GET["arr"]){
	case 'consult':
    		//print_r($_SESSION["arr_consult"]);
		$arr_report = $_SESSION["arr_consult"];
		//$w = array(14,34,34,34,34,20,34,34,34,34,34);
		$w = array(14,34,34,29,22,19,29,34,32,30,64);
		$subtitle = 'GENERAL CONSULTS';
		break;
	case 'ccdev':
		//print_r($_SESSION["arr_ccdev"]);
		$arr_report = $_SESSION["arr_ccdev"];
		$w = array(38,45,38,30,38,38,38,38,38);
		$subtitle = 'CHILD CARE';
		break;
	case 'mc':
		//print_r($_SESSION["arr_mc"]);
		$arr_report = $_SESSION["arr_mc"];
		$w = array(28,40,28,28,28,25,28,31,28,20,28,30);
		$subtitle = 'MATERNAL CARE';
		break;
	default:
		print "<font color='red'>Cannot produce report. Parameter is not accepted.</font>";
		break;
    }
    
    $header = $_SESSION["tbl_header"] = $arr_report[0];
    $contents = $arr_report[1];
    $_SESSION["record_count"] = $arr_report[2];
    $_SESSION["col_width"] = $w;
    $_SESSION["subtitle"] = $subtitle;

    $pdf->SetFont('Arial','',8);
    $pdf->AddPage();
    $pdf->ShowTable($header,$contents);
    /*$pdf->AddPage();
    $pdf->ImprovedTable($header,$data);
    $pdf->AddPage();
    $pdf->FancyTable($header,$data);
    */
    $pdf->Output();
?>