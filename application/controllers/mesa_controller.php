<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Mesa_controller extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('mesa_model');
		$this->load->model('material_model');
	}

	public function index()
	{

		if($this->session->userdata('logged_in')){

			$session_data = $this->session->userdata('logged_in');
			$data['username'] = $session_data['username'];
			$data['mesas'] = $this->mesa_model->get_mesas();
			$this->load->view('templates/default', $data);
			$this->load->view('mesas/index', $data);
			$this->load->view('templates/footer', $data);
		}else{
			redirect('inicio/index', 'refresh');
		}
	}

	public function view($id)
	{

		if($this->session->userdata('logged_in')){

			$data['mesa'] = $this->mesa_model->get_mesas($id);

			if (empty($data['mesa']))
			{
			show_404();
			}

            $session_data = $this->session->userdata('logged_in');
			$data['username'] = $session_data['username'];

            $this->load->view('templates/default', $data);
			$this->load->view('mesas/view', $data);
			$this->load->view('templates/footer', $data);
		}else{
			redirect('inicio/index', 'refresh');
		}
	}

	public function add($id = 0) { //utilizamos el add y el edit para lo mismo
		
		if($this->session->userdata('logged_in')){

            $session_data = $this->session->userdata('logged_in');
		    $data['username'] = $session_data['username'];

		    if($id != 0){
		    	$data['mesa'] = $this->mesa_model->get_mesas($id);
		    }

            $this->load->view('templates/default', $data);
			$this->load->view('mesas/add', $data);
			$this->load->view('templates/footer');
        }else{
            redirect('inicio/index', 'refresh');
        }
	}


	public function addBD() {
		

		if($this->session->userdata('logged_in')){

			$session_data = $this->session->userdata('logged_in');
			$data['username'] = $session_data['username'];

			$this->load->library('form_validation');
			$this->form_validation->set_rules('nombre', 'nombre', 'trim|required|xss_clean');
			$this->form_validation->set_rules('cantidad_personas', 'cantidad_personas', 'num|required|xss_clean');
			$this->form_validation->set_rules('comentarios', 'comentarios', 'trim|required|xss_clean');


			if($this->form_validation->run() == FALSE){

				//$data['errores'] = "Campos incorrectos";

				$this->load->view('templates/default', $data);
				$this->load->view('mesas/add', $data);
				$this->load->view('templates/footer');
			}else{
				//convierto a fecha el string
				//$fecha = Date("Y-m-d H:i:s", strtotime($this->input->post('fecha_reserva')));
				//$fecha =  $fecha->format('Y-m-d H:i:s');

				$datos = array(
				   'nombre' => $this->input->post('nombre') ,
				   'cantidad_personas' => $this->input->post('cantidad_personas') ,
				   'idioma' => $this->input->post('idioma'),
				   'activa' => 1,
				   'fecha_reserva' => $this->input->post('fecha_reserva'),
				   'comentarios' => $this->input->post('comentarios')
				);

				if($this->input->post('id_mesa') != ""){
					$id = $this->input->post('id_mesa');
					$this->mesa_model->update_mesa($datos, $id);
				}else{
					$this->mesa_model->add_mesa($datos);
				}

				redirect('mesa/index', 'refresh');
			}
		

		}else{
			redirect('inicio/index', 'refresh');
		}
		
	}

	public function delete($id) { //utilizamos el add y el edit para lo mismo
		
		if($this->session->userdata('logged_in')){

            $session_data = $this->session->userdata('logged_in');
		    $data['username'] = $session_data['username'];


		    $datos = array(
				   'activa' => 0
				);

		    if($id != 0){
		    	$data['mesa'] = $this->mesa_model->delete_mesa($datos, $id);
		    }

            redirect('mesa/index', 'refresh');
        }else{
            redirect('inicio/index', 'refresh');
        }
	}

	public function searchDes() {
		
		if($this->session->userdata('logged_in')){

            //$session_data = $this->session->userdata('logged_in');
		    //$data['username'] = $session_data['username'];

		    $mesas = $this->mesa_model->search_mesa();
		    
		    echo json_encode($mesas);
 
		}else{
            redirect('inicio/index', 'refresh');
        }
	}
	
	public function exportPdf( $id ){
		
		if($this->session->userdata('logged_in')){
			
			$this->load->library('Pdf');
			$iva = $this->config->item('iva');
			$pdf = new Pdf('P', 'mm', 'A4', true, 'UTF-8', false);
			
			// set document information
			$pdf->SetCreator(PDF_CREATOR);
			$pdf->SetAuthor('Fernando');
			$pdf->SetTitle('Exportación a PDF');
			$pdf->SetSubject('TCPDF');
			//$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

			// set default header data
			$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, "Reserva de Mesa Nº: ".$id. "  ".PDF_HEADER_TITLE, PDF_HEADER_STRING);

			// set header and footer fonts
			$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
			$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

			// set default monospaced font
			$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

			// set margins
			$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
			$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
			$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

			// set auto page breaks
			$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

			// set image scale factor
			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

			// ---------------------------------------------------------

			// set default font subsetting mode
			$pdf->setFontSubsetting(true);

			// Set font
			$pdf->SetFont('helvetica', '', 14, '', true);

			// Add a page
			// This method has several options, check the source code documentation for more information.
			$pdf->AddPage();

			// Set some content to print
			$center_pdf = '<table border="1" cellspacing="3" cellpadding="4" width="100%">
					<tr>
					<td colspan="2" width="100%" height="5%">M<sup>a</sup> Rosa Basora Compte - DNI 39843772-K</td>
					</tr>
					<tr>
					<td colspan="2" width="100%" height="5%">&nbsp;</td>
					</tr>
					<tr>
					<td width="75%" height="7%" VALIGN=TOP >  NOMBRE</td>
					<td width="25%" height="7%" VALIGN=TOP>  nif/cif/pasaporte</td>
					</tr>
					<tr>
					<td width="100%" colspan="2" height="7%" VALIGN=TOP >  DOMICILIO</td>
					</tr>
					<tr>
					<td width="100%" colspan="2" height="7%" VALIGN=TOP >  POBLACIÓN</td>
					</tr>
					</table>';

			$mesaReservada = $this->mesa_model->get_mesas( $id );

			
			$fechaReserva = "Reserva realizada el día: ".$mesaReservada['fecha_reserva'];

			/*$productos_cab_pdf = '<table border="0" width="100%" cellspacing="0" cellpadding="0">*/
			$productos_cab_pdf = '<table border="1" cellspacing="3" cellpadding="4"><thead><tr><th>CANTIDAD</th><th>CONCEPTO</th><th>PRECIO</th><th>IMPORTE</th></tr></thead><tbody>';
					
			$pdf->writeHTML($center_pdf, true, false, true, false, '');
			//$pdf->writeHTML('<br>', true, false, true, false, '');
			
			$pdf->writeHTML($fechaReserva, true, false, true, false, '');
			$pdf->writeHTML('<br>', true, false, true, false, '');
			
			$precio_base = 0;
			$precio_total = 0;	
			//recupera productos de la mesa
			$materiales_mesa = $this->material_model->get_materiales_mesa( $id );
			if( count($materiales_mesa) > 0 ){
				$contenido = "";
				foreach( $materiales_mesa as $mat ){
					
					$precio = 0;
					$precio = (int)$mat['cantidad'] * ($mat['precio'] + 0);
					$precio_base = $precio_base + $precio;
					
					$contenido = $contenido.'<tr>
						<td>'.$mat['cantidad'].'</td>
						<td>'.$mat['nombre_es'].'</td>
						<td>'.$mat['precio'].'&nbsp; &euro;</td>
						<td>'.$precio.'&nbsp; &euro;</td>
					</tr>';
				}
			}else{
				$contenido = '<tr><td colspan="4">No hay productos</td></tr>';
			}
			
			$pdf->writeHTML($productos_cab_pdf.$contenido."</tbody></table>", true, false, true, false, '');
			
			$precio_total = $precio_base + ($precio_base * ($iva / 100));
			
			$footer_pdf = '<table border="1" cellspacing="3" cellpadding="4" width="70%">
		<tr>
		<th align=cente>BASE</tH>
		<th align=center>IVA %</tH>
		<th align=center>T. FACTURA</tH>
		</tr>
		<tr>
		<td align=center>'.$precio_base.' &nbsp; &euro;</td>
		<td align=center>'.$iva.' &nbsp; %</td>
		<td align=center>'.$precio_total.' &nbsp; &euro;</td>
		</tr></table>';
		
		$pdf->writeHTML('<br>', true, false, true, false, '');
		$pdf->writeHTML('<br>', true, false, true, false, '');	
		$pdf->writeHTML($footer_pdf, true, false, true, false, '');
			
			// ---------------------------------------------------------

			// Close and output PDF document
			// This method has several options, check the source code documentation for more information.
			
			$pdf->Output('factura.pdf', 'I');
			
		}else{
            redirect('inicio/index', 'refresh');
        }
		
	}

}
		
