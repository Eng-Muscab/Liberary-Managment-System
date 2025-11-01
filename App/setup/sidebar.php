<div id="sidebar" class="active">
    <div class="sidebar-wrapper active">
        <div class="sidebar-header">
            <div class="d-flex justify-content-between">
                <div class="logo">
                    <a href="index.html"><img src="assets/images/logo/logo.png" alt="Logo" srcset=""></a>
                </div>
                <div class="toggler">
                    <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
                </div>
            </div>
        </div>
        <div class="sidebar-menu">
            <ul class="menu">
                <li class="sidebar-item active">
                    <a href="index.html" class='sidebar-link'>
                        <i class="bi bi-grid-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>   

                <?php

                require("config.php");

                $sql = "SELECT `menu_id`, `menu_name`, `table_name`, `menu_icon`, `parent_id`, `menu_order`, `is_active` FROM `sidebar_menu` WHERE parent_id = 0 and menu_id != 1";
                $result = $conn->query($sql);

                while ($row = $result->fetch_array()) {
                    if ($row['menu_name'] == 'Reports') {
                ?>

                        <li class="sidebar-item">
                            <a href="#" class='sidebar-link'>
                                <i class="<?php echo $row['menu_icon']; ?>"></i>
                                <span><?php echo $row['menu_name']; ?></span>
                            </a>
                        <?php
                    } else {
                        ?>
                        <li class="sidebar-item has-sub">
                            <a href="#" class='sidebar-link'>
                                <i class="<?php echo $row['menu_icon']; ?>"></i>
                                <span><?php echo $row['menu_name']; ?></span>
                            </a>
                            <ul class="submenu">
                                <?php

                                require("config.php");

                                $sql2 = "SELECT s2.* FROM sidebar_menu s, sidebar_menu s2 WHERE s.menu_id = s2.parent_id and s2.parent_id = '" . $row['menu_id'] . "'";
                                $result1 = $conn->query($sql2);

                                while ($row1 = $result1->fetch_array()) {
                                ?>
                                    <li class="submenu-item">
                                        <a class="get_page" href="./setup/generation.php?table=<?php echo $row1['table_name']; ?>">
                                            <i class="<?php echo $row1['menu_icon']; ?>"></i>
                                            <span><?php echo $row1['menu_name']; ?></span>
                                        </a>
                                    </li>
                                <?php } ?>
                            </ul>
                        </li>

                <?php }
                } ?>

            </ul>
        </div>
    </div>
</div>