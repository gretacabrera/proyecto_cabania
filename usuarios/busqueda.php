<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Usuarios&ruta=usuarios" style="margin-bottom: 20px;">
    
    <div style="margin-bottom: 15px;">
        <label>Nombre de usuario:</label>
        <input type="text" name="usuario_nombre" value="<?php if (isset($_REQUEST["usuario_nombre"])){ echo htmlspecialchars($_REQUEST["usuario_nombre"]); } ?>" style="margin-left: 10px;">
    </div>
    
    <div style="margin-bottom: 15px;">
        <label>Perfil:</label>
        <select name="rela_perfil" style="margin-left: 10px;">
            <option value="">Seleccione un perfil...</option>
            <?php
                $registros = $mysql->query("select * from perfil where perfil_estado = 1") or
                die($mysql->error);
                
                while ($row = $registros->fetch_assoc()) {
                    echo "<option value='".$row["id_perfil"]."'";
                    if (isset($_REQUEST["rela_perfil"])){
                        if ($_REQUEST["rela_perfil"] == $row["id_perfil"]){
                            echo " selected";
                        }
                    }
                    echo ">".$row["perfil_descripcion"]."</option>";
                }
            ?>
        </select>
    </div>
    
    <div style="margin-bottom: 15px;">
        <input type="submit" value="Buscar" class="btn-search">
        <input type="button" value="Limpiar" onclick="limpiarFormulario(this)" style="margin-left: 10px;">
        
        <?php if (isset($_REQUEST["usuario_nombre"]) && $_REQUEST["usuario_nombre"] != "" || isset($_REQUEST["rela_perfil"]) && $_REQUEST["rela_perfil"] != ""): ?>
            <a href="/proyecto_cabania/plantilla_modulo.php?titulo=Usuarios&ruta=usuarios" style="margin-left: 10px; text-decoration: none;">
                <button type="button" style="background-color: #dc3545; color: white; padding: 6px 12px; border: none; border-radius: 4px;">Ver todos</button>
            </a>
        <?php endif; ?>
    </div>
</form>