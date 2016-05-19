/**
 * Copyright (c) 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

'use strict';

require('ZedGui');
require('../../sass/main.scss');

$(document).ready(function(){
   $('#create-discount-button').on('click', function() {
         $('#discount-form').submit();
   });
});
