<?php 
namespace assets\libs;

use PDO;
use PDOException;
/**
 * @author Crespo Jhonatan
 * @since 14/08/2019
 */
class Balance
{

	protected $con;
	
	protected $perfiles = array(
		'Hotel'           => 1,
		'Franquiciatario' => 2,
		'Referidor'       => 3,
		'Sistema'         => 4,
		'Promotor'        => 5
	);

	protected $sale = array(
		'id'                  => null,
		'username'            => null,
		'total'               => null,
		'commission'          => null,
		'eSmarties'           => null,
		'referral_id'         => null,
		'referral_commission' => 0,
		'certificate_id'      => null
	);

	protected $is_reserva = false;
	protected $reservacion = array(
		'id_reserva'  => null,
		'registrante' => null
	);
	
	function __construct(connection $conexion){
		$this->con = $conexion->con;
	}

	private function capturarultimobalancesistema(){

		$query = "SELECT balance from balance where perfil =:perfilp order by id desc LIMIT 1";
		$stm = $this->con->prepare($query);
		$stm->execute(array(':perfilp'=>$this->perfiles['Sistema']));
		$balance = $stm->fetch(PDO::FETCH_ASSOC)['balance'];
		if($balance > 0){
			return $balance;
		}else{
			return 0;
		}

	}

	private function capturarultimobalancehotel(int $idhotel){
		$query = "SELECT balance from balance where id_hotel =:hotel order by id desc LIMIT 1";
		$stm = $this->con->prepare($query);
		$stm->execute(array(':hotel'=>$idhotel));
		$balance = $stm->fetch(PDO::FETCH_ASSOC)['balance'];
		if($balance > 0){
			return $balance;
		}else{
			return 0;
		}
	}


	private function capturarultimobalancepromotor(){
		$query = "SELECT balance from balance where id_promotor =:promotor and perfil = :perfilp order by id desc LIMIT 1";
		$stm = $this->con->prepare($query);
		$stm->execute(array(':promotor'=>$this->reservacion['registrante'],':perfilp'=>$this->perfiles['Promotor']));
		$balance = $stm->fetch(PDO::FETCH_ASSOC)['balance'];
		if($balance > 0){
			return $balance;
		}else{
			return 0;
		}
	}

	private function capturarultimobalancefranquiciatario(int $idfranquiciatario){
		$query = "SELECT balance from balance where id_franquiciatario =:franquiciatario order by id desc LIMIT 1";
		$stm = $this->con->prepare($query);
		$stm->execute(array(':franquiciatario'=>$idfranquiciatario));
		$balance = $stm->fetch(PDO::FETCH_ASSOC)['balance'];
		if($balance > 0){
			return $balance;
		}else{
			return 0;
		}
	}

	private function capturarultimobalancereferidor(int $idreferidor){
		$query = "SELECT balance from balance where id_referidor =:referidor order by id desc LIMIT 1";
		$stm = $this->con->prepare($query);
		$stm->execute(array(':referidor'=>$idreferidor));
		$balance = $stm->fetch(PDO::FETCH_ASSOC)['balance'];
		if($balance > 0){
			return $balance;
		}else{
			return 0;
		}
	}

	protected function newBalanceSistema(int $idventa,$tp){

			$ultimobalance = $this->capturarultimobalancesistema();
			$comisionsistema = $tp;
					
			$balance = $comisionsistema + $ultimobalance;

			$query = "insert into balance(balance,id_venta,comision,perfil) values(:balance,:venta,:comision,:perfil)";
			$stm  = $this->con->prepare($query);
			$stm->execute(array(':balance'=>$balance,
										':venta'=>$idventa,
										':comision'=>$comisionsistema,
										':perfil' => $this->perfiles['Sistema']
									));

	}


	protected function newBalanceHotel(int $idventa,$tp){

		$query = 'SELECT h.id as hotel, h.comision from 
						hotel as h join huespedhotel as hh on h.id = hh.id_hotel
							join huesped as hu on hh.id_huesped = hu.id
							where hu.id_usuario = :usuario';
				$stm = $this->con->prepare($query);

				$stm->execute(array(':usuario'=>$this->sale['id']));

				$filas = $stm->fetch(PDO::FETCH_ASSOC);
				$idhotel = $filas['hotel'];
				$comisionhotel = $filas['comision'];

				if($idhotel > 0){

					if(!is_null($this->reservacion['registrante'])){

						$ultimobalance = $this->capturarultimobalancepromotor();
						$comisionhotelnew = ($tp * $comisionhotel / 100);
						$balance = $comisionhotelnew + $ultimobalance;

						$query = 'INSERT into balance(balance,id_promotor,id_venta,comision,perfil)values(:balance,:promotor,:venta,:comision,:perfilp)';
						$stm  = $this->con->prepare($query);
						$stm->execute(array(':balance'=>$balance,
											':promotor'=>$this->reservacion['registrante'],
											':venta'=>$idventa,
											':comision'=>$comisionhotelnew,
											':perfilp'=>$this->perfiles['Promotor']
											));

					}else{

						$ultimobalance = $this->capturarultimobalancehotel($idhotel);
						$comisionhotelnew = ($tp * $comisionhotel / 100);
						$balance = $comisionhotelnew + $ultimobalance;

						$query = "insert into balance(balance,id_hotel,id_venta,comision,perfil) values(:balance,:hotel,:venta,:comision,:perfilp)";
						$stm  = $this->con->prepare($query);
						$stm->execute(array(':balance'=>$balance,':hotel'=>$idhotel,
											':venta'=>$idventa,
											':comision'=>$comisionhotelnew,
											':perfilp'=>$this->perfiles['Hotel']
											));
					}
				}
		}



		protected function newBalanceFranquiciatario(int $idventa){

		$query = 'SELECT fr.id as franquiciatario, fr.comision from 
						franquiciatario as fr join hotel as h on fr.id_hotel  = h.id 
							join huespedhotel as hh on h.id = hh.id_hotel 
							join huesped as hu on hh.id_huesped = hu.id 
							where hu.id_usuario = :usuario';
				$stm = $this->con->prepare($query);
				$stm->execute(array(':usuario'=>$this->sale['id']));

				$filas = $stm->fetch(PDO::FETCH_ASSOC);
				$idfranquiciatario = $filas['franquiciatario'];
				$comisionfranquiciatario = $filas['comision'];

				if($idfranquiciatario > 0){
				
					$ultimobalance = $this->capturarultimobalancefranquiciatario($idfranquiciatario);
					$comisionfranquiciatarionew = ($this->sale['eSmarties'] * $comisionfranquiciatario / 100);
					$balance = ($this->sale['eSmarties'] * $comisionfranquiciatario / 100) + $ultimobalance;

					$query = "insert into balance(balance,id_franquiciatario,id_venta,comision,perfil) values(:balance,:franquiciatario,:venta,:comision,:perfilp)";
					$stm  = $this->con->prepare($query);
					$stm->execute(array(':balance'=>$balance,':franquiciatario'=>$idfranquiciatario,
										':venta'=>$idventa,':comision'=>$comisionfranquiciatarionew,':perfilp'=>$this->perfiles['Franquiciatario']));
				}
		}

		protected function newBalanceReferidor(int $idventa){

		$query = 'SELECT rf.id as referidor, rf.comision from 
						referidor as rf join hotel as h on rf.id_hotel  = h.id 
							join huespedhotel as hh on h.id = hh.id_hotel 
							join huesped as hu on hh.id_huesped = hu.id 
							where hu.id_usuario = :usuario';

				$stm = $this->con->prepare($query);
				$stm->execute(array(':usuario'=>$this->sale['id']));

				$filas = $stm->fetch(PDO::FETCH_ASSOC);
				$idReferidor = $filas['referidor'];
				$comisionreferidor = $filas['comision'];

				if($idReferidor > 0){
				
					$ultimobalance = $this->capturarultimobalancereferidor($idReferidor);
					$comisionreferidornew = ($this->sale['eSmarties'] * $comisionreferidor / 100);
					$balance = ($this->sale['eSmarties'] * $comisionreferidor / 100) + $ultimobalance;

					$query = "insert into balance(balance,id_referidor,id_venta,comision,perfil) values(:balance,:referidor,:venta,:comision,:perfilp)";
					$stm  = $this->con->prepare($query);
					$stm->execute(array(':balance'=>$balance,':referidor'=>$idReferidor,
										':venta'=>$idventa,':comision'=>$comisionreferidornew,':perfilp'=>$this->perfiles['Referidor']));
				}
		}

		



	
}

 ?>