<div id="top_bar" class="navbar navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container">
            <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>

            <a class="brand" href="<?= $path('/') ?>">Scratch</a>

            <?php if ($var('connected', true)): ?>
                <div class="btn-group pull-right">
                <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                    <i class="icon-user"></i>
                    <?= $var('username', 'John Doe') ?>
                    <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                    <li>
                        <a href="<?= $path('/') ?>" id="link_account">
                            My account
                        </a>
                    </li>
                    <li class="divider"></li>
                    <li>
                        <a href="<?= $path('/') ?>" id="link_my_workspace">
                            My workspace
                        </a>
                    </li>
                    <li>
                        <a href="<?= $path('/') ?>" id="link_agenda">
                            My agenda
                        </a>
                    </li>
                    <li>
                        <a href="<?= $path('/') ?>" id="link_my_resources">
                            My resources
                        </a>
                    </li>
                    <li>
                        <a href="<?= $path('/') ?>" id="link_my_contacts">
                            My contacts
                        </a>
                    </li>
                    <li class="divider"></li>
                    <li>
                        <a href="<?= $path('/') ?>" id="link_my_notes">
                            My notes
                        </a>
                    </li>
                    <li>
                        <a href="<?= $path('/') ?>" id="link_portfolio">
                            My portfolio
                        </a>
                    </li>
                    <li class="divider"></li>
                    <li>
                        <a href="<?= $path('/') ?>" id="link_logout">
                            Logout
                        </a>
                    </li>
                    </ul>
                </div>
                <div class="btn-group pull-right">
                    <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                        Workspaces
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li>Workspace 1</li>
                        <li>Workspace 2</li>
                        <li>Workspace 3</li>
                        <!--
                        {% for workspace in workspaces %}
                            <li>
                                <a href="{{path('claro_ws_show', {'workspaceId': workspace.getId()})}}" id="link_workspace_{{ workspace.getId() }}">
                                {{workspace.getCode()}} - {{ workspace.getName() }}
                                </a>
                            </li>
                        {% endfor %}
                        -->
                        <li class="divider"></li>
                        <li>
                            <a href="<?= $path('/') ?>" id="link_all_workspaces">
                                All workspaces
                            </a>
                        </li>
                    </ul>
                </div>
            <?php else: ?>
                <div class="nav-collapse">
                    <ul class="nav pull-right">
                    <?php if ($var('registerTarget', 'foo') !== null): ?>
                        <li>
                            <a id="link_registration" href="<?= $path('/') ?>">
                                Register
                            </a>
                        </li>
                    <?php endif; ?>
                        <li>
                            <a href="<?= $path('/') ?>" id="link_login">
                                Login
                            </a>
                        </li>
                    </ul>
                </div>
            <?php endif; ?>
            <?php if ($var('connected', true)): ?>
                <div class="nav-collapse">
                    <ul class="nav">
                        <li>
                            <a href="<?= $path('/') ?>" id="link_dashboard">
                                Desktop
                            </a>
                        </li>
                        <?php if ($var('isAdmin', true)): ?>
                            <li>
                                <a href="<?= $path('/') ?>" id="link_administration">
                                    Administration
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
