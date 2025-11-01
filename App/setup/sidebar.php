<div id="sidebar" class="active">
    <div class="sidebar-wrapper active">
        <div class="sidebar-header">
            <!-- <div class="d-flex justify-content-between">
                <div class="logo">
                    <a href="index.html"><img src="assets/images/logo/books.png" alt="book" srcset="">Library MS</a>
                </div>
                <div class="toggler">
                    <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
                </div>
            </div> -->
            <div class="d-flex justify-content-between align-items-center px-3 py-2 rounded 
            bg-light text-dark bg-dark-subtle text-light-emphasis">
                <div class="logo d-flex align-items-center gap-2">
                    <a href="index.html" class="d-flex align-items-center text-decoration-none text-reset">
                        <img
                            src="assets/images/logo/books.png"
                            alt="book"
                            class="img-fluid me-2"
                            style="width: 50px; height: 50px;">
                        <span class="fw-bold fs-4">
                            <span class="text-primary">Maktuub</span>
                            <span class="text-body-emphasis">LM</span>
                        </span>

                    </a>
                </div>

                <div class="toggler">
                    <a href="#" class="sidebar-hide d-xl-none d-block text-secondary fs-4">
                        <i class="bi bi-x-lg"></i>
                    </a>
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
                    } else{
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