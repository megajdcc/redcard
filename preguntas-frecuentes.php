<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
$con = new assets\libs\connection();

$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array('options' => array('default' => 1, 'min_range' => 1)));
$total = 34; $rpp = 10;
$page = min((int)ceil($total / $rpp), $page);
$pagination = new assets\libraries\pagination\pagination($page, $total);
$pagination->setRPP($rpp);
$pager = $pagination->parse();

$includes = new assets\libs\includes($con);
$properties['title'] = 'Preguntas Frecuentes | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_main_navbar(); ?>
	<div class="main">
		<div class="main-inner">
			<div class="content">
				<div class="mt-80">
					<div class="document-title">
						<h1 class="text-binary">Preguntas Frecuentes</h1>
					</div><!-- /.document-title -->
				</div>
				<div class="container">
					<?php echo $con->get_notify();?>
					<div class="row">
						<div class="col-sm-12">
							<div class="faq text-default">
<?php if($page == 1){ ?>
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Qu&eacute; es Travel Points?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											<strong>Travel Points</strong> es un programa de lealtad de afiliaci&oacute;n totalmente gratuita, que une m&uacute;ltiples negocios y compradores en una sola plataforma.
										</p>
										<p>
											En un solo sitio los compradores encuentran negocios, promociones y regalos, lo que lo convierte en una app muy conveniente antes de ir a comprar cualquier cosa.
										</p>
										<p>
											Los clientes registran sus compras en los negocios afiliados y a cambio obtienen un porcentaje de su compra en puntos (esmartties) que podr&aacute; canjear por otros productos o servicios de la tienda Travel Points.
										</p>
										<p>
											Es el club que recompensa las compras de los socios en los negocios afiliados.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Qui&eacute;n puede ser socio?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Cualquier persona que compre puede ser socio. 
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Cu&aacute;nto cuesta ser socio?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Es gratis. La afiliaci&oacute;n es totalmente gratuita, y te brinda beneficios desde el momento en que te inscribes.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Debo comprar o vender algo para ser socio?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											No. No existen compras ni ventas obligatorias. Es un club que recompensa los consumos de los socios en los negocios afiliados.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿C&oacute;mo me hago socio del club?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Llena el sencillo formulario y listo. Si alguien te invit&oacute;, agrad&eacute;cele anotando su nombre de usuario en el formulario de afiliaci&oacute;n. Te llegar&aacute; un email para validar tu correo y confirmar tu afiliaci&oacute;n. Si no te llega, b&uacute;scalo en la carpeta de no deseados.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Qu&eacute; gano por ser socio?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Como socio tienes total acceso a todos los beneficios y regalos del CLUB. Destacan los siguientes:
										</p>
										<ol type="a">
											<li>
												<strong>Acceso al cat&aacute;logo de negocios afiliados</strong>. Podr&aacute;s ver todos los negocios que est&aacute;n recompensando a quienes realizan compras con ellos
											</li>
											<li>
												<strong>Acceso a la verdad</strong>. Ver&aacute;s la opini&oacute;n de los dem&aacute;s socios de Travel Points que ya consumieron o compraron en los negocios afiliados; y (2) emitir tu propia opini&oacute;n respecto de alg&uacute;n negocio afiliado (4) acceso a eventos y promociones exclusivas para socios.
											</li>
											<li>
												<strong>Promociones exclusivas para socios</strong>. Los negocios publican promociones exclusivas a los socios a trav&eacute;s del sitio de eSmartClub.
											</li>
											<li>
												<strong>Puntos</strong>. Recibes una recomensa en eSmartties por cada compra que registres equivalente al porcentaje publicado por cada uno de los negocios del monto de tu consumo.
											</li>
											<li>
												<strong>M&aacute;s puntos</strong>. Recibes otra recompensa por cada compra que realicen las personas que t&uacute; invitaste al club. Puedes ver tus puntos en “Tu Perfil”.
											</li>
											<li>
												<strong>Certificados y Cupones</strong>. Como socio obtienes los certificados y cupones que los Negocios Afiliados ofrecen en exclusiva a los socios del Club. Encuentra los que te gusten y &uacute;salos.
											</li>
											<li>
												<strong>Monedero electr&oacute;nico personal</strong> donde ir&aacute;s acumulando los puntos ganados. 
											</li>
											<li>
												<strong>Control de los puntos ganados</strong> y de los regalos adquiridos. 
											</li>
										</ol>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿C&oacute;mo me identifico como socio?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Con tu nombre o tu nombre de usuario. ¡As&iacute; de f&aacute;cil!
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿En qu&eacute; momento debo identificarme como socio del Travel Points?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Para registrar una compra, necesitas identificarte. Menciona tu nombre, o tu usuario al vendedor en el momento de realizar tu pago y pedir que se registre tu compra. ¡As&iacute; de f&aacute;cil! Por ejemplo: soy socio de Travel Points, registre mi compra a nombre de “juanperez”. Si acualizaste tu perfil con tu foto, el negocio podr&aacute; identificarte f&aacute;cilmente.
										</p>
										<p>
											Si vas a usar alg&uacute;n beneficio exclusivo, es conveniente que te identifiques como socio desde tu llegada al negocio, para que el vendedor sepa qu&eacute; regalo o promoci&oacute;n deber&aacute; otorgarte.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿C&oacute;mo obtengo una tarjeta de socio?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Puedes adquirir tu tarjeta personalizada en la tienda Travel Points en el sitio.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿C&oacute;mo obtengo mi regalo (el que un negocio ofrece a trav&eacute;s de un certificado)?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Inicia sesi&oacute;n en tu cuenta y selecciona el certificado que quieres para que se agregue a tu lista de deseos. La cantidad de certificados disponibles que ofrece cada negocio son limitados y se descuentan en la medida que los socios los consumen, por lo que te recomendamos te apresures a canjearlo.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
<?php } if($page == 2){ ?>
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿C&oacute;mo canjeo los regalos/certificados/cupones que tengo en mi cuenta?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Cuando visites el Negocio Afiliado que otorg&oacute; el beneficio, identif&iacute;cate como socio e informa de tu inter&eacute;s en canjear tu beneficio ¡y listo.
										</p>
										<p>
											Toma siempre en cuenta la vigencia que el Negocio Afiliado establece para otorgar sus beneficios.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Cu&aacute;ndo caducan mis certificados de regalo/cupones?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											La caducidad de cada uno de los beneficios las establecen los Negocios Afiliados, y est&aacute;n escritas en el mismo.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Qu&eacute; pasa si cuando llego al negocio ya no quedan certificados disponibles (otros socios los consumieron antes que yo)?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Puedes ver la cantidad actualizada de certificados disponibles en el sitio de esmartclub.com  Al llegar al negocio menciona que te interesa canjear el certificado. Si por cualquier motivo no quedasen certificados disponibles del que quieres, verifica otros certificados que haya publicado el negocio, act&iacute;valo y canj&eacute;alo. A criterio, decisi&oacute;n y posibilidad del negocio, podr&aacute; cargar m&aacute;s certificados del que t&uacute; quer&iacute;as, pero quiz&aacute; no tengan m&aacute;s...as&iacute; que se tolerante.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿C&oacute;mo gano puntos?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Ganas puntos (los llamamos eSmartties) cada vez que registras un consumo en cualquiera de los Negocios Afiliados, Tambi&eacute;n ganas eSmartties cuando cualquiera de tus invitados registra un consumo en cualquiera de los negocios del club. Puedes ver tu saldo en “Tu Perfil” dentro de tu cuenta de <strong>Travel Points</strong>.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿C&oacute;mo me gasto mis eSmartties?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Accede a tu cuenta eSmart, y visita la tienda de Travel Points. Puedes comprar cualquiera de los productos y servicios publicados como en una tienda normal.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Cu&aacute;ntos eSmartties puedo ganar?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											La suma de eSmartties es ilimitada. Ganar&aacute;s eSmartties. Cada compra registrada te abona el porcentaje del monto de tu compra ofrecido por el negocio (y publicado en el sitio de esmartclub.com en el momento del registro). Al registrar la compra te llegar&aacute; una notificaci&oacute;n por email de manera inmediata de los eSmartties ganados por ese consumo. Verifica que coincida antes de irte del negocio.
										</p>
										<p>
											Tambi&eacute;n ganar&aacute;s eSmartties cada vez que tus invitados realicen compras en los negocios afiliados.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Por qu&eacute; deber&iacute;a invitar a socios?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Cuando un socio te refiere durante el momento de su inscripci&oacute;n (en una casilla del formulario) ganar&aacute;s el 10% de todos los eSmartties que ellos ganan. Por cuestiones de privacidad no sabr&aacute;s de qu&eacute; socio te llegan los eSmartties, pero ver&aacute;s el incremento de tu saldo de manera inmediata.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Mis datos personales est&aacute;n seguros?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Tus datos personales son confidenciales y est&aacute;n protegidos por la Ley Federal de Protecci&oacute;n de Datos Personales en posesi&oacute;n de particulares publicada en el Diario Oficial de la Federaci&oacute;n el 5 de julio de 2010.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿C&oacute;mo invito a mis amigos?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Existen dos maneras de invitar a tus amigos:
										</p>
										<ol>
											<li>
												Ingresa a tu cuenta de eSmart, a la secci&oacute;n Mi Perfil y env&iacute;a una invitaci&oacute;n por email.
											</li>
											<li>
												Pide a tus amigos que entren a esmartclub.com, y que abran una cuenta gratuita. P&iacute;deles que pongan tu nombre o tu usuario en la casilla correspondiente para que quedes registrado como la persona que los invit&oacute;.
											</li>
										</ol>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Tiene costo la aplicaci&oacute;n de eSmart?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											No. <strong>Travel Points</strong> es un sistema totalmente gratuito.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
<?php } if($page == 3){ ?>
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Puedo invitar negocios al Club?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											S&iacute;. Deber&aacute;s invitar al due&ntilde;o del negocio, y solo &eacute;l podr&aacute; crear la cuenta de negocio. Invita a tus negocios favoritos para que comiencen a darte eSmartties. 
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Qu&eacute; tipo de negocios pueden afiliarse?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Cualquier tipo de negocio que venda productos o servicios. El sistema pide una direcci&oacute;n f&iacute;sica. Pueden participar todos los giros l&iacute;citos que se apeguen a los t&eacute;rminos y condiciones del club.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Qu&eacute; pasa si un negocio no cumple con un beneficio?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Cualquier socio que no reciba el beneficio ofrecido podr&aacute; denunciar al Negocio Afiliado. La denuncia es p&uacute;blica y podr&aacute; ser vista por todos los socios. Si el negocio reincide <strong>Travel Points</strong> podr&aacute; proceder de acuerdo a los t&eacute;rminos y condiciones del club.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿C&oacute;mo puedo saber si me est&aacute;n pagando las comisiones de mis invitados?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											El sistema Travel Points es autom&aacute;tico. Todos los registros de compras de tus invitados te generar&aacute;n eSmartties autom&aacute;ticamente y te llegar&aacute;n de inmediato.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Qu&eacute; hago si detecto que no se registr&oacute; mi consumo?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											<strong>Travel Points</strong> te env&iacute;a un aviso por email cada vez que registras un consumo en un Negocio Afiliado. Si no lo recibes en el momento notifica al negocio, o en su caso, ponte en contacto con el club para que acredites tu consumo o lo denuncies en los t&eacute;rminos y condiciones del club.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Qu&eacute; beneficios obtiene mi negocio si lo afilio?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<ol type="a">
											<li>
												<strong>Promoci&oacute;n</strong>. El negocio acceder&aacute; de manera gratuita al poderoso sistema Travel Points, y podr&aacute; publicar sus productos, servicios, eventos y promociones a toda la comunidad de socios y a todos los visitantes del sitio Travel Points.
												<p>Las herramientas promocionales incluyen:</p>
												<ol>
													<li>
														Creaci&oacute;n de su <strong>eSmart Page</strong>, donde podr&aacute; publicar im&aacute;genes y videos de su negocio, productos y servicios que ofrece, caracter&iacute;sticas, ventajas competitivas, horarios de trabajo, video, palabras clave, y sus datos de contacto. Usted podr&aacute; actualizar su eSmart Page todas las veces que quiera. Su eSmart Page publicar&aacute; de manera autom&aacute;tica los comentarios y evaluaciones que realicen los socios que hayan consumido en su negocio.
													</li>
													<li>
														Listado de su negocio en el Directorio de Negocios de eSmart.
													</li>
													<li>
														Vinculaci&oacute;n de su eSmart Page en el buscador de Travel Points.
													</li>
													<li>
														Posibilidad de que los socios lo sigan (ver&aacute;n todas sus publicaciones en su perfil privado).
													</li>
													<li>
														Publicaci&oacute;n gratuita de sus promociones a los socios que lo est&eacute;n siguiendo.
													</li>
													<li>
														Publicaci&oacute;n en el sistema de los beneficios exclusivos que ofrece a los socios.
													</li>
													<li>
														Recibir&aacute; invitaciones para formar parte de los eventos tem&aacute;ticos organizados por el club que apliquen a su negocio, con el objetivo de generarle flujo de clientes.
													</li>
													<li>
														Geo-localizaci&oacute;n de su negocio a trav&eacute;s de la aplicaci&oacute;n (los smartphones notificar&aacute;n a su due&ntilde;o de la cercan&iacute;a de su negocio).
													</li>
													<li>
														B&uacute;squeda de su negocio por palabras clave, en el buscador de <strong>eSmart</strong>.
													</li>
													<li>
														Posicionamiento en el Cat&aacute;logo de Negocios, con base en el porcentaje ofrecido. A mayor porcentaje, el negocio se posiciona en los primeros lugares.
													</li>
												</ol>
											</li>
											<li>
												<strong>Clientes</strong>. El sistema Travel Points pone su negocio al alcance de locales y turistas, y brinda una exposici&oacute;n masiva de sus productos y servicios, permitiendo generar y crecer una audiencia leal que mantendr&aacute; contacto con su negocio a trav&eacute;s del sistema de comunicaci&oacute;n interno.
												<p>
													Como Travel Points es un sistema que se basa en la promoci&oacute;n de la verdad, el solo hecho de encontrar su negocio afiliado, genera en los consumidores la credibilidad de que sus productos y servicios ser&aacute;n de la calidad ofrecida.
												</p>
												<p>
													Como <strong>Travel Points</strong> es un sistema que se basa en la promoci&oacute;n de la verdad, el solo hecho de encontrar su negocio afiliado, genera en los consumidores la credibilidad de que sus productos y servicios ser&aacute;n de la calidad ofrecida.
												</p>
											</li>
											<li>
												<strong>Incremento de sus ventas</strong>. Su negocio obtendr&aacute; dinero de dos fuentes.
											</li>
											<li>
												<strong> Lealtad de sus clientes</strong>. Gracias a los eSmartties que el negocio ofrece, sus clientes regresar&aacute;n a su negocio una y otra vez adem&aacute;s de recomendarlo a sus amigos y familiares (nuevos clientes).
											</li>
										</ol>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">Qu&eacute; pasa si un socio denuncia con falsedad a mi negocio</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											El Negocio Afiliado al ver una denuncia que no es verdad, o que est&aacute; fuera de lugar, tiene derecho a r&eacute;plica. Travel Points proceder&aacute; en los t&eacute;rminos y condiciones del club.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Cu&aacute;nto es la comisi&oacute;n que el Negocio Afiliado debe pagar?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											6% o superior. La comisi&oacute;n m&iacute;nima es del 6% pero usted podr&aacute; ofrecer m&aacute;s de manera voluntaria en cualquier momento. Al incrementar el monto de la comisi&oacute;n, ganar&aacute; la atenci&oacute;n de m&aacute;s socios que lo elegir&aacute;n (ya que ellos ganan ese monto en eSmartties en cada consumo en su negocio). Los Negocios Afiliados se publican con base en un algoritmo que toma el porcentaje de comisi&oacute;n ofrecido, de tal manera que el que m&aacute;s comisi&oacute;n ofrece, aparece el primero de la lista.
										<p>
											La cantidad que usted ofrece al sistema como comisi&oacute;n, es la cantidad que Travel Points otorga a sus socios en eSmartties.
										</p>
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿C&oacute;mo pago las comisiones al sistema Travel Points?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Al afiliarse el negocio tiene un cr&eacute;dito a favor. Cargue saldo (con una tarjeta de cr&eacute;dito, d&eacute;bito o paypal) desde su Panel de Administraci&oacute;n dentro de su cuenta en Travel Points o si lo prefiere ll&aacute;menos para facilitarle un n&uacute;mero de cuenta.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿C&oacute;mo pago las comisiones de las ventas generadas?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Las comisiones se descuentan autom&aacute;ticamente de su saldo. Mantenga en su cuenta fondos suficientes para el pago de sus comisiones. Los negocios cuentan con un cr&eacute;dito preautorizado desde la autorizaci&oacute;n de su registro al club.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
<?php } if($page == 4){ ?>
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Qu&eacute; pasa si me quedo sin fondos para pagar las comisiones?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											El negocio tiene un cr&eacute;dito pre-autorizado (como la reserva de su tanque de gasolina) de $500 pesos. Si registra una venta, se compromete a pagar ese monto al sistema a trav&eacute;s de una recarga de saldo. No circule en reserva, ¡cargue saldo! En caso de que una venta a un socio no pueda realizarse por falta de fondos, el sistema no permitir&aacute; el registro y esto afectar&aacute; el grado de satisfacci&oacute;n y credibilidad de su cliente. Si llegara a suceder acuerde con el socio la fecha de registro de su consumo. Si el negocio no paga lo acordado al sistema, su cuenta ser&aacute; bloqueada y posteriormente suspendida.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿C&oacute;mo pago mi cr&eacute;dito?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Cuando cargue saldo a su cuenta se cubre el saldo del cr&eacute;dito preautorizado utilizado y si hubiera un excedente, se deposita al monto disponible del saldo en su cuenta. 
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Puedo suspender la promoci&oacute;n de mi negocio temporalmente?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											S&iacute;, Informa al club y te anunciaremos como “Cerrado por Temporada” manteniendo toda tu informaci&oacute;n, pero sin que sea p&uacute;blica.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿C&oacute;mo puedo cancelar mi cuenta?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											En cualquier momento socios y negocios pueden cancelar su cuenta desde su cuenta Travel Points desde su Perfil. Los Negocios deber&aacute;n respetar los Certificados otorgados y que se encuentren vigentes.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
<?php } ?>
							</div><!-- /.faq -->
							<?php echo $pager;?><!-- /.pagination -->
						</div><!-- /.col-* -->
					</div><!-- /.row -->
				</div><!-- /.container -->
			</div><!-- /.content -->
		</div><!-- /.main-inner -->
	</div><!-- /.main -->
<?php echo $footer = $includes->get_main_footer(); ?>