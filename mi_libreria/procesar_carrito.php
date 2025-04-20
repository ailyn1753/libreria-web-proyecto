<?php
session_start();
require_once 'db_connect.php';

// Verificar sesión de usuario
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado.']),
    exit;
}
$user_id = $_SESSION['user_id'];

// Verificar que la acción fue enviada por POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];
    $pdo = connectDB();
    $response = ['success' => false, 'message' => 'Acción no válida.'];

    try {
        $pdo->beginTransaction(); // Iniciar transacción

        switch ($action) {
            case 'add':
                if (isset($_POST['libro_id'], $_POST['cantidad'])) {
                    $libro_id = filter_var($_POST['libro_id'], FILTER_VALIDATE_INT);
                    $cantidad = filter_var($_POST['cantidad'], FILTER_VALIDATE_INT);

                    if ($libro_id && $cantidad && $cantidad > 0) {
                        // Verificar si el libro ya está en el carrito del usuario
                        $sql_check = "SELECT ID, cantidad FROM CARRITO WHERE ID_usuario = :user_id AND ID_libro = :libro_id LIMIT 1";
                        $stmt_check = $pdo->prepare($sql_check);
                        $stmt_check->execute([':user_id' => $user_id, ':libro_id' => $libro_id]);
                        $item_existente = $stmt_check->fetch();

                        if ($item_existente) {
                            // Si existe, actualizar la cantidad
                            $nueva_cantidad = $item_existente['cantidad'] + $cantidad;
                            $sql_update = "UPDATE CARRITO SET cantidad = :cantidad WHERE ID = :carrito_id";
                            $stmt_update = $pdo->prepare($sql_update);
                            $stmt_update->execute([':cantidad' => $nueva_cantidad, ':carrito_id' => $item_existente['ID']]);
                            $response = ['success' => true, 'message' => 'Cantidad actualizada en el carrito.'];
                        } else {
                             // Si no existe, insertar nuevo item (Obtener precio del libro primero)
                             $sql_precio = "SELECT precio FROM LIBROS WHERE ID = :libro_id LIMIT 1";
                             $stmt_precio = $pdo->prepare($sql_precio);
                             $stmt_precio->execute([':libro_id' => $libro_id]);
                             $libro_info = $stmt_precio->fetch();

                             if($libro_info) {
                                 $monto_total = $libro_info['precio'] * $cantidad; // Calcular monto
                                 $sql_insert = "INSERT INTO CARRITO (ID_usuario, ID_libro, cantidad, monto_total) VALUES (:user_id, :libro_id, :cantidad, :monto)";
                                 $stmt_insert = $pdo->prepare($sql_insert);
                                 $stmt_insert->execute([
                                     ':user_id' => $user_id,
                                     ':libro_id' => $libro_id,
                                     ':cantidad' => $cantidad,
                                     ':monto' => $monto_total
                                 ]);
                                  $response = ['success' => true, 'message' => 'Libro agregado al carrito.'];
                             } else {
                                  $response['message'] = 'Libro no encontrado.';
                             }
                        }
                    } else {
                         $response['message'] = 'Datos inválidos para agregar.';
                    }
                } else {
                     $response['message'] = 'Faltan datos para agregar.';
                }
                break;

             case 'update':
                if (isset($_POST['carrito_id'], $_POST['cantidad'])) {
                    $carrito_id = filter_var($_POST['carrito_id'], FILTER_VALIDATE_INT);
                    $cantidad = filter_var($_POST['cantidad'], FILTER_VALIDATE_INT);

                    if ($carrito_id && $cantidad && $cantidad > 0) {
                        // Actualizar cantidad (y recalcular monto si es necesario)
                        // Primero obtener precio unitario para recalcular monto total del item
                        $sql_precio = "SELECT l.precio FROM CARRITO c JOIN LIBROS l ON c.ID_libro = l.ID WHERE c.ID = :carrito_id AND c.ID_usuario = :user_id LIMIT 1";
                        $stmt_precio = $pdo->prepare($sql_precio);
                        $stmt_precio->execute([':carrito_id' => $carrito_id, ':user_id' => $user_id]);
                        $item_info = $stmt_precio->fetch();

                        if($item_info) {
                             $monto_total_actualizado = $item_info['precio'] * $cantidad;
                             $sql_update = "UPDATE CARRITO SET cantidad = :cantidad, monto_total = :monto WHERE ID = :carrito_id AND ID_usuario = :user_id";
                             $stmt_update = $pdo->prepare($sql_update);
                             $stmt_update->execute([
                                 ':cantidad' => $cantidad,
                                 ':monto' => $monto_total_actualizado,
                                 ':carrito_id' => $carrito_id,
                                 ':user_id' => $user_id
                             ]);
                             if ($stmt_update->rowCount() > 0) {
                                  $response = ['success' => true, 'message' => 'Cantidad actualizada.'];
                             } else {
                                 $response['message'] = 'No se pudo actualizar o el item no pertenece al usuario.';
                             }
                        } else {
                             $response['message'] = 'Item del carrito no encontrado.';
                        }

                    } else {
                         $response['message'] = 'Datos inválidos para actualizar.';
                    }
                } else {
                     $response['message'] = 'Faltan datos para actualizar.';
                }
                break;

            case 'delete':
                if (isset($_POST['carrito_id'])) {
                    $carrito_id = filter_var($_POST['carrito_id'], FILTER_VALIDATE_INT);

                    if ($carrito_id) {
                        // Eliminar item del carrito
                        $sql_delete = "DELETE FROM CARRITO WHERE ID = :carrito_id AND ID_usuario = :user_id";
                        $stmt_delete = $pdo->prepare($sql_delete);
                        $stmt_delete->bindParam(':carrito_id', $carrito_id, PDO::PARAM_INT);
                        $stmt_delete->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                        $stmt_delete->execute();

                        if ($stmt_delete->rowCount() > 0) {
                             $response = ['success' => true, 'message' => 'Item eliminado del carrito.'];
                        } else {
                            $response['message'] = 'No se pudo eliminar o el item no pertenece al usuario.';
                        }
                    } else {
                        $response['message'] = 'ID de item inválido.';
                    }
                } else {
                    $response['message'] = 'Falta ID del item a eliminar.';
                }
                break;

            default:
                 $response['message'] = 'Acción desconocida.';
                break;
        }

        $pdo->commit(); // Confirmar transacción si todo fue bien

    } catch (PDOException $e) {
        $pdo->rollBack(); // Revertir cambios en caso de error
        error_log("Error DB Carrito: " . $e->getMessage());
         $response = ['success' => false, 'message' => 'Error al procesar la solicitud del carrito.'];
    } finally {
        $pdo = null; // Cerrar conexión
    }

     // Redirigir de vuelta a la página del carrito
     $_SESSION['cart_feedback'] = $response; // Guardar feedback en sesión
     header('Location: carrito_form.php'); // O la página donde esté el carrito
     exit;

} else {
    // Si no es POST o no hay acción, redirigir
    header('Location: index.php');
    exit;
}
?>