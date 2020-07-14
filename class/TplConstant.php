<?php
/**
 * Copyright (c) 2020. ComboStrap, Inc. and its affiliates. All Rights Reserved.
 *
 * This source code is licensed under the GPL license found in the
 * COPYING  file in the root directory of this source tree.
 *
 * @license  GPL 3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 * @author   ComboStrap <support@combostrap.com>
 *
 */

namespace ComboStrap;

/**
 * Class TplConf
 * @package ComboStrap
 * A repository of constant (configuration and default)
 */
class TplConstant
{
    const CONF_USE_CDN = "useCDN";
    const CONF_GRID_COLUMNS = "gridColumns";


    const CONF_REM_SIZE = "remSize";
    const CONF_BOOTSTRAP_STYLESHEET = "bootstrapStylesheet";
    const CONF_BOOTSTRAP_VERSION = "bootstrapVersion";
    const DEFAULT_BOOTSTRAP_STYLESHEET = "bootstrap.min.css";
    /**
     * The bar are also used to not add a {@link \action_plugin_combo_metacanonical}
     *
     */
    const CONF_FOOTER = "footerbar";
    const CONF_HEADER = "headerbar";
    const CONF_SIDEKICK = "sidekickbar";


}
