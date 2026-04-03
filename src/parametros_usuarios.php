<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../config/db.php';
    $db = new DatabaseConnection();
    
    $pdo_cuentas = $db->getCuentasConnection();
    $pdo_info = $db->getInfoConnection();
    $pdo_inventario = $db->getInvConnection();

    $accion = $_POST['accion'] ?? '';
    $tipo = $_POST['tipo_entidad'] ?? '';
    $id_item = $_POST['id_elemento'] ?? null;

    try {
        if ($accion === 'agregar') {
            if ($tipo === 'sede') {
                $stmt = $pdo_cuentas->prepare("INSERT INTO sedes (GLOSA_FISCALIA) VALUES (?)");
                $stmt->execute([$_POST['glosa_sede']]);
            } elseif ($tipo === 'software') {
                $stmt = $pdo_cuentas->prepare("INSERT INTO software (GLOSA_SOFTWARE, ESTADO_SOFTWARE) VALUES (?, ?)");
                $stmt->execute([$_POST['glosa_software'], $_POST['estado_software']]);
            } elseif ($tipo === 'usuario') {
                $usr_id_manual = !empty($_POST['usr_id']) ? (int)$_POST['usr_id'] : null;
                $mostrar_intranet = isset($_POST['usr_mostrar_intranet']) && $_POST['usr_mostrar_intranet'] !== '' ? (int)$_POST['usr_mostrar_intranet'] : 0;
                $usr_cod_unidad = !empty($_POST['usr_cod_unidad']) ? (int)$_POST['usr_cod_unidad'] : 0;
                
                if ($usr_id_manual) {
                    $stmt = $pdo_info->prepare("INSERT INTO usuarios (id, cod_fiscalia, cod_unidad, rut, usuario, nombre, telefono, estado, cargo, equipo, perfil, correo_electronico, fec_nacimiento, mostrar_intranet, fec_inicio_funciones, fec_termino_funciones, adm_intranet, fiscal_func, ip, tipo_usuario) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $usr_id_manual,
                        $_POST['usr_sede'] ?: 0, $usr_cod_unidad, $_POST['usr_rut'] ?? '',
                        $_POST['usr_usuario'] ?? '', $_POST['usr_nombre'] ?? '', $_POST['usr_telefono'] ?? '',
                        $_POST['usr_estado'] ?: 0, $_POST['usr_cargo'] ?? '', $_POST['usr_equipo'] ?: 0,
                        $_POST['usr_perfil'] ?: 0, $_POST['usr_correo'] ?? '', !empty($_POST['usr_fec_nac']) ? $_POST['usr_fec_nac'] : null,
                        $mostrar_intranet, !empty($_POST['usr_fec_ini']) ? $_POST['usr_fec_ini'] : null,
                        !empty($_POST['usr_fec_fin']) ? $_POST['usr_fec_fin'] : null, $_POST['usr_adm_intranet'] ?: 0,
                        !empty($_POST['usr_fiscal_func']) ? $_POST['usr_fiscal_func'] : null, $_POST['usr_ip'] ?? '',
                        !empty($_POST['usr_tipo']) ? $_POST['usr_tipo'] : null
                    ]);
                    $nuevo_id_usuario = $usr_id_manual;
                } else {
                    $stmt = $pdo_info->prepare("INSERT INTO usuarios (cod_fiscalia, cod_unidad, rut, usuario, nombre, telefono, estado, cargo, equipo, perfil, correo_electronico, fec_nacimiento, mostrar_intranet, fec_inicio_funciones, fec_termino_funciones, adm_intranet, fiscal_func, ip, tipo_usuario) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $_POST['usr_sede'] ?: 0, $usr_cod_unidad, $_POST['usr_rut'] ?? '',
                        $_POST['usr_usuario'] ?? '', $_POST['usr_nombre'] ?? '', $_POST['usr_telefono'] ?? '',
                        $_POST['usr_estado'] ?: 0, $_POST['usr_cargo'] ?? '', $_POST['usr_equipo'] ?: 0,
                        $_POST['usr_perfil'] ?: 0, $_POST['usr_correo'] ?? '', !empty($_POST['usr_fec_nac']) ? $_POST['usr_fec_nac'] : null,
                        $mostrar_intranet, !empty($_POST['usr_fec_ini']) ? $_POST['usr_fec_ini'] : null,
                        !empty($_POST['usr_fec_fin']) ? $_POST['usr_fec_fin'] : null, $_POST['usr_adm_intranet'] ?: 0,
                        !empty($_POST['usr_fiscal_func']) ? $_POST['usr_fiscal_func'] : null, $_POST['usr_ip'] ?? '',
                        !empty($_POST['usr_tipo']) ? $_POST['usr_tipo'] : null
                    ]);
                    $nuevo_id_usuario = $pdo_info->lastInsertId();
                }

                if ($usr_cod_unidad > 0) {
                    $stmt_uni = $pdo_info->prepare("INSERT INTO usuario_unidad (cod_usuario, cod_unidad, estado_usuario_uni) VALUES (?, ?, 1)");
                    $stmt_uni->execute([$nuevo_id_usuario, $usr_cod_unidad]);
                }

                $inv_sede = !empty($_POST['inv_sede']) ? (int)$_POST['inv_sede'] : null;
                $inv_edificio = !empty($_POST['inv_edificio']) ? (int)$_POST['inv_edificio'] : null;
                $inv_division = !empty($_POST['inv_division']) ? (int)$_POST['inv_division'] : null;

                if ($inv_sede && $inv_edificio && $inv_division) {
                    $max_stmt = $pdo_inventario->query("SELECT MAX(ID_UBICACION) FROM ubicaciones");
                    $max_id = $max_stmt->fetchColumn();
                    $nuevo_id_ubi = $max_id ? $max_id + 1 : 1;
                    $glosa_ubicacion = $_POST['usr_usuario']; 
                    $tipo_ubicacion = 'USUARIOS';
                    $stmt_ubi = $pdo_inventario->prepare("INSERT INTO ubicaciones (ID_UBICACION, FISCALIA_UBICACION, EDIFICIO_UBICACION, DIVISION_UBICACION, GLOSA_UBICACION, TIPO, ID_USUARIO_ASIGNADO) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt_ubi->execute([$nuevo_id_ubi, $inv_sede, $inv_edificio, $inv_division, $glosa_ubicacion, $tipo_ubicacion, $nuevo_id_usuario]);
                }
            }
        } elseif ($accion === 'modificar') {
            if ($tipo === 'sede') {
                $stmt = $pdo_cuentas->prepare("UPDATE sedes SET GLOSA_FISCALIA = ? WHERE ID_SEDE = ?");
                $stmt->execute([$_POST['glosa_sede'], $id_item]);
            } elseif ($tipo === 'software') {
                $stmt = $pdo_cuentas->prepare("UPDATE software SET GLOSA_SOFTWARE = ?, ESTADO_SOFTWARE = ? WHERE ID_SOFTWARE = ?");
                $stmt->execute([$_POST['glosa_software'], $_POST['estado_software'], $id_item]);
            } elseif ($tipo === 'usuario') {
                $mostrar_intranet = isset($_POST['usr_mostrar_intranet']) && $_POST['usr_mostrar_intranet'] !== '' ? (int)$_POST['usr_mostrar_intranet'] : 0;
                $usr_cod_unidad = !empty($_POST['usr_cod_unidad']) ? (int)$_POST['usr_cod_unidad'] : 0;
                
                $stmt = $pdo_info->prepare("UPDATE usuarios SET cod_fiscalia=?, cod_unidad=?, rut=?, usuario=?, nombre=?, telefono=?, estado=?, cargo=?, equipo=?, perfil=?, correo_electronico=?, fec_nacimiento=?, mostrar_intranet=?, fec_inicio_funciones=?, fec_termino_funciones=?, adm_intranet=?, fiscal_func=?, ip=?, tipo_usuario=? WHERE id=?");
                $stmt->execute([
                    $_POST['usr_sede'] ?: 0, $usr_cod_unidad, $_POST['usr_rut'] ?? '',
                    $_POST['usr_usuario'] ?? '', $_POST['usr_nombre'] ?? '', $_POST['usr_telefono'] ?? '',
                    $_POST['usr_estado'] ?: 0, $_POST['usr_cargo'] ?? '', $_POST['usr_equipo'] ?: 0,
                    $_POST['usr_perfil'] ?: 0, $_POST['usr_correo'] ?? '', !empty($_POST['usr_fec_nac']) ? $_POST['usr_fec_nac'] : null,
                    $mostrar_intranet, !empty($_POST['usr_fec_ini']) ? $_POST['usr_fec_ini'] : null,
                    !empty($_POST['usr_fec_fin']) ? $_POST['usr_fec_fin'] : null, $_POST['usr_adm_intranet'] ?: 0,
                    !empty($_POST['usr_fiscal_func']) ? $_POST['usr_fiscal_func'] : null, $_POST['usr_ip'] ?? '',
                    !empty($_POST['usr_tipo']) ? $_POST['usr_tipo'] : null, $id_item
                ]);

                $stmt_check_uni = $pdo_info->prepare("SELECT cod_usuario_uni FROM usuario_unidad WHERE cod_usuario = ?");
                $stmt_check_uni->execute([$id_item]);
                
                if ($stmt_check_uni->fetchColumn()) {
                    if ($usr_cod_unidad > 0) {
                        $stmt_upd_uni = $pdo_info->prepare("UPDATE usuario_unidad SET cod_unidad = ? WHERE cod_usuario = ?");
                        $stmt_upd_uni->execute([$usr_cod_unidad, $id_item]);
                    } else {
                        $stmt_del_uni = $pdo_info->prepare("DELETE FROM usuario_unidad WHERE cod_usuario = ?");
                        $stmt_del_uni->execute([$id_item]);
                    }
                } else {
                    if ($usr_cod_unidad > 0) {
                        $stmt_ins_uni = $pdo_info->prepare("INSERT INTO usuario_unidad (cod_usuario, cod_unidad, estado_usuario_uni) VALUES (?, ?, 1)");
                        $stmt_ins_uni->execute([$id_item, $usr_cod_unidad]);
                    }
                }
            }
        } elseif ($accion === 'anular') {
            if ($tipo === 'sede') {
                $pdo_cuentas->prepare("DELETE FROM sedes WHERE ID_SEDE = ?")->execute([$id_item]);
            } elseif ($tipo === 'software') {
                $pdo_cuentas->prepare("DELETE FROM software WHERE ID_SOFTWARE = ?")->execute([$id_item]);
            } elseif ($tipo === 'usuario') {
                $stmt_sel = $pdo_info->prepare("SELECT usuario FROM usuarios WHERE id = ?");
                $stmt_sel->execute([$id_item]);
                $user_del = $stmt_sel->fetchColumn();
                
                if ($user_del) {
                    $foto_path_nuevo = $_SERVER['DOCUMENT_ROOT'] . "/SIUGI/public/avatar/" . strtolower($user_del) . ".jpg";
                    if (file_exists($foto_path_nuevo)) @unlink($foto_path_nuevo);
                }
                
                $pdo_info->prepare("DELETE FROM usuarios WHERE id = ?")->execute([$id_item]);
                $pdo_info->prepare("DELETE FROM usuario_unidad WHERE cod_usuario = ?")->execute([$id_item]);
            }
        }
        
        if ($tipo === 'usuario' && in_array($accion, ['agregar', 'modificar'])) {
            $user_nick = strtolower($_POST['usr_usuario'] ?? '');
            
            $dir_fotos_nuevo = $_SERVER['DOCUMENT_ROOT'] . '/SIUGI/public/avatar/'; 
            
            if (!empty($user_nick)) {
                $ruta_destino_nuevo = $dir_fotos_nuevo . $user_nick . '.jpg';
                
                if ($accion === 'modificar' && isset($_POST['eliminar_foto'])) {
                    if (file_exists($ruta_destino_nuevo)) @unlink($ruta_destino_nuevo);
                }
                
                if (isset($_FILES['foto_usuario']) && $_FILES['foto_usuario']['error'] === UPLOAD_ERR_OK) {
                    
                    if (!is_dir($dir_fotos_nuevo)) {
                        @mkdir($dir_fotos_nuevo, 0775, true);
                    }
                    
                    $ext = strtolower(pathinfo($_FILES['foto_usuario']['name'], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg'])) {
                        $tmp_name = $_FILES['foto_usuario']['tmp_name'];
                        
                        if (copy($tmp_name, $ruta_destino_nuevo)) {
                            @unlink($tmp_name); 
                            
                            $ruta_windows = str_replace('/', '\\', $ruta_destino_nuevo);
                            @exec("icacls \"$ruta_windows\" /q /c /reset");
                        }
                    }
                }
            }
        }
        
        $timestamp = time();
        header("Location: /SIUGI/parametros_usuarios?status=success&msg=" . urlencode("Registro procesado correctamente") . "&v=" . $timestamp);
        exit;
    } catch (Exception $e) {
        $timestamp = time();
        header("Location: /SIUGI/parametros_usuarios?status=error&msg=" . urlencode($e->getMessage()) . "&v=" . $timestamp);
        exit;
    }
} else {
    header("Location: /SIUGI/parametros_usuarios");
    exit;
}