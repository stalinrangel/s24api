<!DOCTYPE html>
<html>
    <head>
        <title>Documentacion 24managerAPI</title>

    </head>
    <body>	
		<h1 style="text-align: center;">Documentacion constructoraKienAPI</h1>

		<h3>-----Login-----</h3>

		<h4>Login Web</h4>
		<p>Metodo para hacer login desde el panel administrativo.</p>
		<p>URL: http://localhost/gitHub/proyConstructoraKien/constructoraKienAPI/public/login/web</p>
		<p>Metodo: POST</p>
		<p>Headers:</p>
		<p>Cuerpo de consulta (body):</p>
		<ul>
			<li>user (Requerido): Usuario</li>
			<li>password (Requerido)</li>
		</ul>

		<h4>Login App</h4>
		<p>Metodo para hacer login desde la app.</p>
		<p>URL: http://localhost/gitHub/proyConstructoraKien/constructoraKienAPI/public/login/app</p>
		<p>Metodo: POST</p>
		<p>Headers:</p>
		<p>Cuerpo de consulta (body):</p>
		<ul>
			<li>user (Requerido): Usuario</li>
			<li>password (Requerido)</li>
		</ul>

		<h3>-----Validacion de token-----</h3>

		<h4>Validar token</h4>
		<p>Metodo para validar un token que se pasa en el cuerpo de la consulta.</p>
		<p>URL: http://localhost/gitHub/proyConstructoraKien/constructoraKienAPI/public/validar/token</p>
		<p>Metodo: POST</p>
		<p>Headers:</p>
		<p>Cuerpo de consulta (body):</p>
		<ul>
			<li>token (Requerido)</li>
		</ul>

		<h3>-----Gestion de olvido de password-----</h3>

		<h4>Generar codigo de verificacion</h4>
		<p>Metodo para generar un codigo aleatorio de verificacion para el usuario con el email de la url con una validez de 5 min.</p>
		<p>URL: http://localhost/gitHub/proyConstructoraKien/constructoraKienAPI/public/password/cliente/{email}</p>
		<p>Metodo: GET</p>
		<p>Headers:</p>
		<p>Cuerpo de consulta (body):</p>

		<h4>Validar codigo de verificaion</h4>
		<p>Metodo para obtener acceso al cambio de password, mediante el codigo de verificacion de la url.</p>
		<p>URL: http://localhost/gitHub/proyConstructoraKien/constructoraKienAPI/public/password/codigo/{codigo}</p>
		<p>Metodo: GET</p>
		<p>Headers:</p>
		<p>Cuerpo de consulta (body):</p>
		

		<h3>-----Gestion de usuarios-----</h3>

		<h4>Obtener usuarios</h4>
		<p>Retorna todos los usuarios administradores (tipo 0) y clientes (tipo 1).</p>
		<p>URL: http://localhost/gitHub/proyConstructoraKien/constructoraKienAPI/public/usuarios</p>
		<p>Metodo: GET</p>
		<p>Headers: Authorization : Bearer + token</p>
		<p>Cuerpo de consulta (body):</p>
		
		<h4>Obtener usuarios con pedidos</h4>
		<p>Retorna todos los usuarios con sus pedidos.</p>
		<p>URL: http://localhost/gitHub/proyConstructoraKien/constructoraKienAPI/public/usuarios/pedidos</p>
		<p>Metodo: GET</p>
		<p>Headers: Authorization : Bearer + token</p>
		<p>Cuerpo de consulta (body):</p>

		<h4>Crear usuario (admin/cliente)</h4>
		<p>Crea un usuario de tipo admin o cliente.</p>
		<p>URL: http://localhost/gitHub/proyConstructoraKien/constructoraKienAPI/public/usuarios</p>
		<p>Metodo: POST</p>
		<p>Headers: Authorization : Bearer + token</p>
		<p>Cuerpo de consulta (body):</p>
		<ul>
			<li>user (Requerido): Usuario</li>
			<li>password (Requerido)</li>
			<li>nombre (Requerido)</li>
			<li>correo (Requerido)</li>
			<li>telefono (Requerido)</li>
			<li>tipo (Requerido): 0 (administrador) 1 (cliente)</li>
		</ul>

		<h4>Crear usuario (cliente)</h4>
		<p>Crea un usuario de tipo cliente.</p>
		<p>URL: http://localhost/gitHub/proyConstructoraKien/constructoraKienAPI/public/clientes</p>
		<p>Metodo: POST</p>
		<p>Headers:</p>
		<p>Cuerpo de consulta (body):</p>
		<ul>
			<li>user (Requerido): Usuario</li>
			<li>password (Requerido)</li>
			<li>nombre (Requerido)</li>
			<li>correo (Requerido)</li>
			<li>telefono (Requerido)</li>
		</ul>

		<h4>Obtener usuario</h4>
		<p>Retorna el usuario usuario_id.</p>
		<p>URL: http://localhost/gitHub/proyConstructoraKien/constructoraKienAPI/public/usuarios/{usuario_id}</p>
		<p>Metodo: GET</p>
		<p>Headers: Authorization : Bearer + token</p>
		<p>Cuerpo de consulta (body):</p>

		<h4>Obtener usuario y sus pedidos</h4>
		<p>Retorna el usuario usuario_id y sus pedidos.</p>
		<p>URL: http://localhost/gitHub/proyConstructoraKien/constructoraKienAPI/public/usuarios/{usuario_id}/pedidos</p>
		<p>Metodo: GET</p>
		<p>Headers: Authorization : Bearer + token</p>
		<p>Cuerpo de consulta (body):</p>

		<h4>Editar usuario</h4>
		<p>Modifica los datos del usuario usuario_id.</p>
		<p>URL: http://localhost/gitHub/proyConstructoraKien/constructoraKienAPI/public/usuarios/{usuario_id}</p>
		<p>Metodo: PUT</p>
		<p>Headers: Authorization : Bearer + token</p>
		<p>Cuerpo de consulta (body):</p>
		<ul>
			<li>user (No requerido): Usuario</li>
			<li>password (No requerido)</li>
			<li>nombre (No requerido)</li>
			<li>correo (No requerido)</li>
			<li>telefono (No requerido)</li>
			<li>sexo (No requerido)</li>
			<li>tipo (No requerido): 0 (administrador) 1 (cliente)</li>
		</ul>

		<h4>Eliminar usuario</h4>
		<p>Elimina el usuario usuario_id.</p>
		<p>URL: http://localhost/gitHub/proyConstructoraKien/constructoraKienAPI/public/usuarios/{usuario_id}</p>
		<p>Metodo: DELETE</p>
		<p>Headers: Authorization : Bearer + token</p>
		<p>Cuerpo de consulta (body):</p>

		<h3>-----Gestion de categorias-----</h3>

		<h4>Obtener categorias</h4>
		<p>Retorna todas las categorias.</p>
		<p>URL: http://localhost/gitHub/proyConstructoraKien/constructoraKienAPI/public/categorias</p>
		<p>Metodo: GET</p>
		<p>Headers: Authorization : Bearer + token</p>
		<p>Cuerpo de consulta (body):</p>

		<h4>Obtener categorias con productos</h4>
		<p>Retorna todas las categorias con sus productos.</p>
		<p>URL: http://localhost/gitHub/proyConstructoraKien/constructoraKienAPI/public/categorias/productos</p>
		<p>Metodo: GET</p>
		<p>Headers: Authorization : Bearer + token</p>
		<p>Cuerpo de consulta (body):</p>

		<h4>Crear categoria</h4>
		<p>Crea una categorias.</p>
		<p>URL: http://localhost/gitHub/proyConstructoraKien/constructoraKienAPI/public/categorias</p>
		<p>Metodo: POST</p>
		<p>Headers: Authorization : Bearer + token</p>
		<p>Cuerpo de consulta (body):</p>
		<ul>
			<li>nombre (Requerido)</li>
			<li>imagen (Requerido)</li>
		</ul>

		<h4>Obtener categoria</h4>
		<p>Retorna la categoria categoria_id.</p>
		<p>URL: http://localhost/gitHub/proyConstructoraKien/constructoraKienAPI/public/categorias/{categoria_id}</p>
		<p>Metodo: GET</p>
		<p>Headers: Authorization : Bearer + token</p>
		<p>Cuerpo de consulta (body):</p>

		<h4>Obtener categoria y sus productos</h4>
		<p>Retorna la categoria categoria_id y sus productos.</p>
		<p>URL: http://localhost/gitHub/proyConstructoraKien/constructoraKienAPI/public/categorias/{categoria_id}/productos</p>
		<p>Metodo: GET</p>
		<p>Headers: Authorization : Bearer + token</p>
		<p>Cuerpo de consulta (body):</p>

		<h4>Editar categoria</h4>
		<p>Modifica los datos de la categoria categoria_id.</p>
		<p>URL: http://localhost/gitHub/proyConstructoraKien/constructoraKienAPI/public/categorias/{categoria_id}</p>
		<p>Metodo: PUT</p>
		<p>Headers: Authorization : Bearer + token</p>
		<p>Cuerpo de consulta (body):</p>
		<ul>
			<li>nombre (No requerido)</li>
			<li>imagen (No requerido)</li>
		</ul>

		<h4>Eliminar categoria</h4>
		<p>Elimina la categoria categoria_id.</p>
		<p>URL: http://localhost/gitHub/proyConstructoraKien/constructoraKienAPI/public/categorias/{categoria_id}</p>
		<p>Metodo: DELETE</p>
		<p>Headers: Authorization : Bearer + token</p>
		<p>Cuerpo de consulta (body):</p>

		<h3>-----Gestion de productos-----</h3>

		<h4>Obtener productos</h4>
		<p>Retorna todos los productos.</p>
		<p>URL: http://localhost/gitHub/proyConstructoraKien/constructoraKienAPI/public/productos</p>
		<p>Metodo: GET</p>
		<p>Headers: Authorization : Bearer + token</p>
		<p>Cuerpo de consulta (body):</p>
		
		<h4>Obtener productos con categoria</h4>
		<p>Retorna todos los productos con la categoria a la que pertenecen.</p>
		<p>URL: http://localhost/gitHub/proyConstructoraKien/constructoraKienAPI/public/productos/categoria</p>
		<p>Metodo: GET</p>
		<p>Headers: Authorization : Bearer + token</p>
		<p>Cuerpo de consulta (body):</p>

		<h4>Crear producto</h4>
		<p>Crea un producto a la caegoria categoria_id.</p>
		<p>URL: http://localhost/gitHub/proyConstructoraKien/constructoraKienAPI/public/productos/{categoria_id}</p>
		<p>Metodo: POST</p>
		<p>Headers: Authorization : Bearer + token</p>
		<p>Cuerpo de consulta (body):</p>
		<ul>
			<li>nombre (Requerido)</li>
			<li>imagen (Requerido)</li>
			<li>costo (Requerido): costo por unidad</li>
			<li>cantidad (Requerido): cantidad de unidades</li>
			<li>unidad (Requerido): Kg por ejemplo</li>
		</ul>

		<h4>Obtener producto</h4>
		<p>Retorna el producto producto_id.</p>
		<p>URL: http://localhost/gitHub/proyConstructoraKien/constructoraKienAPI/public/productos/{producto_id}</p>
		<p>Metodo: GET</p>
		<p>Headers: Authorization : Bearer + token</p>
		<p>Cuerpo de consulta (body):</p>

		<h4>Obtener producto con categoria</h4>
		<p>Retorna el producto producto_id y la categoria a la que pertenece.</p>
		<p>URL: http://localhost/gitHub/proyConstructoraKien/constructoraKienAPI/public/productos/{producto_id}/categoria</p>
		<p>Metodo: GET</p>
		<p>Headers: Authorization : Bearer + token</p>
		<p>Cuerpo de consulta (body):</p>

		<h4>Editar producto</h4>
		<p>Modifica los datos del producto producto_id.</p>
		<p>URL: http://localhost/gitHub/proyConstructoraKien/constructoraKienAPI/public/productos/{producto_id}</p>
		<p>Metodo: PUT</p>
		<p>Headers: Authorization : Bearer + token</p>
		<p>Cuerpo de consulta (body):</p>
		<ul>
			<li>nombre (No requerido)</li>
			<li>imagen (No requerido)</li>
			<li>costo (No requerido)</li>
			<li>cantidad (No requerido)</li>
			<li>unidad (No requerido)</li>
		</ul>

		<h4>Eliminar producto</h4>
		<p>Elimina el producto producto_id.</p>
		<p>URL: http://localhost/gitHub/proyConstructoraKien/constructoraKienAPI/public/productos/{producto_id}</p>
		<p>Metodo: DELETE</p>
		<p>Headers: Authorization : Bearer + token</p>
		<p>Cuerpo de consulta (body):</p>
		
		<h3>-----Gestion de pedidos-----</h3>

		<h4>Obtener pedidos</h4>
		<p>Retorna todos los pedidos.</p>
		<p>URL: http://localhost/gitHub/proyConstructoraKien/constructoraKienAPI/public/pedidos</p>
		<p>Metodo: GET</p>
		<p>Headers: Authorization : Bearer + token</p>
		<p>Cuerpo de consulta (body):</p>
		
		<!-- <h4>Obtener informacion de los pedidos</h4>
		<p>Retorna todos los pedidos con su informacion asociada.</p>
		<p>URL: http://localhost/gitHub/proyConstructoraKien/constructoraKienAPI/public/pedidos/informacion</p>
		<p>Metodo: GET</p>
		<p>Headers: Authorization : Bearer + token</p>
		<p>Cuerpo de consulta (body):</p>

		<h4>Crear pedido</h4>
		<p>Crea un pedido.</p>
		<p>URL: http://localhost/gitHub/proyConstructoraKien/constructoraKienAPI/public/pedidos</p>
		<p>Metodo: POST</p>
		<p>Headers: Authorization : Bearer + token</p>
		<p>Cuerpo de consulta (body):</p>
		<ul>
			<li>direccion (Requerido)</li>
			<li>descripcion (Requerido)</li>
			<li>referencia (Requerido)</li>
			<li>lat (No requerido)</li>
			<li>lng (No requerido)</li>
			<li>categoria_id (Requerido)</li>
			<li>subcategoria_id (Requerido)</li>
			<li>usuario_id (Requerido)</li>
			<li>estado (Requerido)</li>
		</ul>

		<h4>Obtener pedido</h4>
		<p>Retorna el pedido pedido_id.</p>
		<p>URL: http://localhost/gitHub/proyConstructoraKien/constructoraKienAPI/public/pedidos/{pedido_id}</p>
		<p>Metodo: GET</p>
		<p>Headers: Authorization : Bearer + token</p>
		<p>Cuerpo de consulta (body):</p>

		<h4>Obtener pedido con su informacion</h4>
		<p>Retorna el pedido pedido_id con toda su informacion asociada.</p>
		<p>URL: http://localhost/gitHub/proyConstructoraKien/constructoraKienAPI/public/pedidos/{pedido_id}/informacion</p>
		<p>Metodo: GET</p>
		<p>Headers: Authorization : Bearer + token</p>
		<p>Cuerpo de consulta (body):</p>

		<h4>Editar pedido</h4>
		<p>Modifica los datos del pedido pedido_id.</p>
		<p>URL: http://localhost/gitHub/proyConstructoraKien/constructoraKienAPI/public/pedidos/{pedido_id}</p>
		<p>Metodo: PUT</p>
		<p>Headers: Authorization : Bearer + token</p>
		<p>Cuerpo de consulta (body):</p>
		<ul>
			<li>direccion (No requerido)</li>
			<li>descripcion (No requerido)</li>
			<li>referencia (No requerido)</li>
			<li>lat (No requerido)</li>
			<li>lng (No requerido)</li>
			<li>estado (No requerido)</li>
			<li>servicio_id (No Requerido)</li>
		</ul>

		<h4>Eliminar pedido</h4>
		<p>Elimina el pedido pedido_id con su calificacion, si la tiene.</p>
		<p>URL: http://localhost/gitHub/proyConstructoraKien/constructoraKienAPI/public/pedidos/{pedido_id}</p>
		<p>Metodo: DELETE</p>
		<p>Headers: Authorization : Bearer + token</p>
		<p>Cuerpo de consulta (body):</p> -->



    </body>
</html>
