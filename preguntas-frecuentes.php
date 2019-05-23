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
											<strong>Travel Points</strong>s un programa de lealtad totalmente gratuito, que permite a los compradores reunir puntos ganados en las compras en cualquiera de los negocios registrados y cambiarlos por selectos regalos de su preferencia.
										</p>
										<p>
											En un mismo sitio los compradores encuentran negocios, promociones y regalos, lo que lo convierte en una app muy conveniente antes de ir a comprar cualquier cosa.
										</p>
										<p>
											Los negocios ofrecen al comprador un porcentaje de sus compras, y el comprador reúne todos los puntos de los diferentes negocios en su misma cartera digital, para sumar rápidamente los puntos necesarios para adquirir los productos y servicios que desee y que estén publicados en la tienda de Travel Points.
										</p>
										
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Qui&eacute;n puede ser usuario?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Cualquier persona que pueda comprar, puede ser usuario.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Cu&aacute;nto cuesta ser usuario?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Es gratis. La afiliación es totalmente gratuita.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Debo comprar o vender algo para ser usuario?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											No. No existen compras ni ventas obligatorias. Es un club que recompensa los consumos de los usuarios en los negocios afiliados.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Cómo me hago usuario de Travel Points?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Llena el formulario y listo. Si alguien te invitó, agradécele anotando su nombre de usuario en el formulario de afiliación. Te llegará un email para validar tu correo y confirmar tu afiliación. Si no te llega, búscalo en la carpeta de no deseados.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Qu&eacute; gano por ser usuario?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Todos los regalos que ofrecen los negocios están disponibles para todos los usuarios. Como usuario tienes total acceso a todos los beneficios y regalos del Travel Points. Destacan los siguientes:
										</p>
										<ol type="a">
											<li>
												<strong>Acceso al cat&aacute;logo de negocios afiliados</strong>. Podr&aacute;s ver todos los negocios que est&aacute;n recompensando a quienes realizan compras con ellos
											</li>
											<li>
												<strong>Acceso a la verdad</strong>. Verás la opinión de los demás socios de Travel Points que ya consumieron o compraron en los negocios afiliados; y (2) emitir tu propia opinión respecto de algún negocio afiliado (4) acceso a eventos y promociones exclusivas para usuarios.
											</li>
											<li>
												<strong>Promociones exclusivas para socios</strong>. Los negocios publican promociones exclusivas para usuarios a través del sitio de Travel Points.
											</li>
											<li>
												<strong>Puntos</strong>. Recibes una recompensa en Travel Points por cada compra que registres equivalente al porcentaje publicado por cada uno de los negocios del monto de tu consumo.
											</li>
											<li>
												<strong>M&aacute;s puntos</strong>. Recibes otra recompensa por cada compra que realicen las personas que tú invitaste al Programa. Puedes ver tus puntos en “Tu Perfil”.
											</li>
											<li>
												<strong>Certificados y Cupones</strong>. Como usuario obtienes los certificados y cupones que los Negocios Afiliados ofrecen en exclusiva a los socios de Travel Points. Encuentra los que te gusten y úsalos.
											</li>
											<li>
												<strong>Monedero electr&oacute;nico personal</strong> donde irás acumulando los puntos ganados.
											</li>
											<li>
												<strong>Control de los puntos ganados</strong> y de los regalos adquiridos.
											</li>
										</ol>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿C&oacute;mo me identifico como usuario?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Con tu nombre o tu nombre de usuario. ¡Así de fácil!
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿En qué momento debo identificarme como usuario de Travel Points?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Para registrar una compra, necesitas identificarte. Menciona tu nombre, o tu usuario al Negocio en el momento de realizar tu pago y pedir que se registre tu compra. ¡Así de fácil! Por ejemplo: soy socio de Travel Points, registre mi compra a nombre de “juanperez”. Si acualizaste tu perfil con tu foto, el negocio podrá identificarte fácilmente.
										</p>
										<p>
											Si vas a usar algún beneficio exclusivo, es conveniente que te identifiques como socio desde tu llegada al negocio, para que el vendedor sepa qué regalo o promoción deberá otorgarte.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Cómo obtengo mi regalo (el que un negocio ofrece a través de un certificado)?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Inicia sesión en tu cuenta y selecciona el certificado que quieres para que se agregue a tu lista de deseos. La cantidad de certificados disponibles que ofrece cada negocio son limitados y se descuentan en la medida que los socios los consumen, por lo que te recomendamos te apresures a canjearlo.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Cómo canjeo los regalos/certificados/cupones que tengo en mi cuenta?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Cuando visites el Negocio Afiliado que otorgó el beneficio, identifícate como socio e informa de tu interés en canjear tu beneficio ¡y listo!
											Toma siempre en cuenta la vigencia que el Negocio Afiliado establece para otorgar sus beneficios.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
<?php } if($page == 2){ ?>
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Cuándo caducan mis certificados de regalo/cupones?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											La caducidad de cada uno de los beneficios las establecen los Negocios Afiliados, y están escritas en el mismo.
										</p>
										
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Cómo gano puntos?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Ganas puntos (los llamamos Travel Points) cada vez que registras un consumo en cualquiera de los Negocios Afiliados, También ganas Travel Points cuando cualquiera de tus invitados registra un consumo en cualquiera de los negocios del Sitio. Puedes ver tu saldo en “Tu Perfil” dentro de tu cuenta de Travel Points.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Cómo me gasto mis puntos Travel Points?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Accede a tu cuenta, y visita la tienda de Travel Points. Puedes comprar cualquiera de los productos y servicios publicados como en una tienda normal.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Cuántos puntos puedo ganar?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											La suma de Travel Points es ilimitada. Cada compra registrada te abona el porcentaje del monto de tu compra ofrecido por el negocio (y publicado en el sitio de travelpoints.com.mx en el momento del registro). Al registrar la compra te llegará una notificación por email de manera inmediata de los puntos ganados por ese consumo. Verifica que coincida antes de irte del negocio.
										</p>
										<p>
											También ganarás puntos Travel Points cada vez que tus invitados realicen compras en los negocios afiliados.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Por qué debería invitar a nuevos usuarios?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Cuando un usuario te refiere durante el momento de su inscripción (en una casilla del formulario) ganarás el 10% de todos los puntos que ellos ganen. Por cuestiones de privacidad no sabrás de qué socio te llegan los puntos, pero verás el incremento de tu saldo de manera inmediata.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Mis datos personales están seguros?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Tus datos personales son confidenciales y están protegidos por la Ley Federal de Protección de Datos Personales en posesión de particulares publicada en el Diario Oficial de la Federación el 5 de julio de 2010.
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
											 	Ingresa a tu cuenta de Travel Points, a la sección Mi Perfil y envía una invitación por email.
											</li>
											<li>
												2. Pide a tus amigos que entren a travelpoints.com.mx, y que abran una cuenta gratuita. Pídeles que pongan tu nombre o tu usuario en la casilla correspondiente para que quedes registrado como la persona que los invitó.
											</li>
										</ol>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								
<?php } if($page == 3){ ?>
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Puedo invitar negocios a Travel Points?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Sí. Deberás invitar al dueño del negocio, y solo él podrá crear la cuenta de negocio. Invita a tus negocios favoritos para que comiencen a darte Travel Points.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Qu&eacute; tipo de negocios pueden afiliarse?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Cualquier tipo de negocio que venda productos o servicios. El sistema pide una dirección física. Pueden participar todos los giros lícitos que se apeguen a los términos y condiciones de Travel Points.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Qu&eacute; pasa si un negocio no cumple con un beneficio?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Cualquier socio que no reciba el beneficio ofrecido podrá denunciar al Negocio Afiliado. La denuncia es pública y podrá ser vista por todos los socios. Si el negocio reincide Travel Points podrá proceder de acuerdo a los términos y condiciones del Sitio.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Cómo puedo saber si me están pagando las comisiones de mis invitados?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											El sistema Travel Points es automático. Todos los registros de compras de tus invitados te generarán puntos automáticamente y te llegarán de inmediato.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Qu&eacute; hago si detecto que no se registr&oacute; mi consumo?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											<strong>Travel Points</strong> te envía un aviso por email cada vez que registras un consumo en un Negocio Afiliado. Si no lo recibes en el momento notifica al negocio, o en su caso, ponte en contacto con el club para que acredites tu consumo o lo denuncies en los términos y condiciones del Sitio.
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
												<p><strong>Promoci&oacute;n</strong>. El negocio accederá de manera gratuita al poderoso sistema Travel Points, y podrá publicar sus productos, servicios, eventos y promociones a toda la comunidad de socios y a todos los visitantes del sitio Travel Points.</p>
												<p>Las herramientas promocionales incluyen:</p>
												<ol>
													<li>
														Creación de su Travel Points Page, donde podrá publicar imágenes y videos de su negocio, productos y servicios que ofrece, características, ventajas competitivas, horarios de trabajo, video, palabras clave, y sus datos de contacto. Usted podrá actualizar su Travel Points Page todas las veces que quiera. Su Travel Points Page publicará de manera automática los comentarios y evaluaciones que realicen los socios que hayan consumido en su negocio.
													</li>
													<li>
														Listado de su negocio en el Directorio de Negocios de Travel Points.
													</li>
													<li>
														Vinculación de su Travel Points Page en el buscador de Travel Points.
													</li>
													<li>
														Posibilidad de que los socios lo sigan (verán todas sus publicaciones en su perfil privado).
													</li>
													<li>
														Publicación gratuita de sus promociones a los socios que lo estén siguiendo
													</li>
													<li>
														Publicación en el sistema de los beneficios exclusivos que ofrece a los socios.
													</li>
													<li>
														Recibirá invitaciones para formar parte de los eventos temáticos organizados por Travel Points que apliquen a su negocio, con el objetivo de generarle flujo de clientes.
													</li>
													<li>
														Geo-localización de su negocio a través de la aplicación (los smartphones notificarán a su dueño de la cercanía de su negocio).
													</li>
													<li>
														Búsqueda de su negocio por palabras clave, en el buscador de <strong>Travel Points</strong>.
													</li>
													<li>
														Posicionamiento en el Catálogo de Negocios, con base en el porcentaje ofrecido. A mayor porcentaje, el negocio se posiciona en los primeros lugares.
													</li>
												</ol>
											</li>
											<li>
												<strong>Clientes</strong>. El sistema Travel Points pone su negocio al alcance de locales y turistas, y brinda una exposición masiva de sus productos y servicios, permitiendo generar y crecer una audiencia leal que mantendrá contacto con su negocio a través del sistema de comunicación interno.
												<p>
													Como Travel Points es un sistema que se basa en la promoción de la verdad, el solo hecho de encontrar su negocio afiliado, genera en los consumidores la credibilidad de que sus productos y servicios serán de la calidad ofrecida.
												</p>
												<p>
													Como <strong>Travel Points</strong> es un sistema que se basa en la promoción de la verdad, el solo hecho de encontrar su negocio afiliado, genera en los consumidores la credibilidad de que sus productos y servicios serán de la calidad ofrecida.
												</p>
											</li>
											<li>
												<strong>Incremento de sus ventas</strong>. Su negocio obtendrá dinero de dos fuentes.
											</li>
											<li>
												<strong> Lealtad de sus clientes</strong>. Gracias a los puntos Travel Points que el negocio ofrece, sus clientes regresarán a su negocio una y otra vez además de recomendarlo a sus amigos y familiares (nuevos clientes).
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
											El Negocio Afiliado al ver una denuncia que no es verdad, o que está fuera de lugar, tiene derecho a réplica. Travel Points procederá en los términos y condiciones publicados en este Sitio Web.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Cu&aacute;nto es la comisi&oacute;n que el Negocio Afiliado debe pagar?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											6% o superior. La comisión mínima es del 6% pero el negocio podrá ofrecer más de manera voluntaria en cualquier momento. Al incrementar el monto de la comisión, ganará la atención de más socios que lo elegirán (ya que ellos ganan ese monto en Travel Points en cada consumo en su negocio). Los Negocios Afiliados se publican con base en un algoritmo que toma el porcentaje de comisión ofrecido, de tal manera que el que más comisión ofrece, aparece el primero de la lista.
										<p>
											La cantidad que usted ofrece al sistema como comisión, es la cantidad que Travel Points otorga a sus socios en puntos.
										</p>
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Cómo pago las comisiones al sistema Travel Points?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Al afiliarse el negocio tiene un crédito a favor. Cargue saldo (con una tarjeta de crédito, débito o paypal) desde su Panel de Administración dentro de su cuenta en Travel Points o si lo prefiere llámenos para facilitarle un número de cuenta.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿C&oacute;mo pago las comisiones de las ventas generadas?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Las comisiones se descuentan automáticamente de su saldo. Mantenga en su cuenta fondos suficientes para el pago de sus comisiones. Los negocios cuentan con un crédito preautorizado desde la autorización de su registro al Sitio Web.
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
											El negocio cuenta con un crédito pre-autorizado de $500 pesos. Si registra una venta, se compromete a pagar ese monto al sistema a través de una recarga de saldo. Si consume su crédito su cuenta se bloquea y los compradores no podrán encontrarlo por lo que es altamente recomendable siempre tener saldo a favor. Si consume su crédito no podrá realizar más ventas. Esto afectará el grado de satisfacción y credibilidad de su cliente. Si llegara a suceder acuerde con el socio la fecha de registro de su consumo. Si el negocio no paga lo acordado al sistema, su cuenta será bloqueada y posteriormente suspendida.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿C&oacute;mo pago mi cr&eacute;dito?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Cuando cargue saldo a su cuenta se cubre el saldo del crédito pre-autorizado utilizado y si hubiera un excedente, se deposita al monto disponible del saldo en su cuenta. 
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿Puedo suspender la promoci&oacute;n de mi negocio temporalmente?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											Sí, informa a Travel Points y te anunciaremos como “Cerrado por Temporada” manteniendo toda tu información, pero sin que sea pública.
										</p>
									</div><!-- /.faq-item-answer -->
								</div><!-- /.faq-item -->
								<div class="faq-item">
									<div class="faq-item-question">
										<h2 class="text-primary">¿C&oacute;mo puedo cancelar mi cuenta?</h2>
									</div><!-- /.faq-item-question -->
									<div class="faq-item-answer">
										<p>
											En cualquier momento socios y negocios pueden cancelar su cuenta desde su cuenta Travel Points desde su Perfil. Los Negocios deberán respetar los Certificados otorgados y que se encuentren vigentes.
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