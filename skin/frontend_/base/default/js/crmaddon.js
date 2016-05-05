/**
 *
 * CART2QUOTE CONFIDENTIAL
 * __________________
 *
 *  [2009] - [2015] Cart2Quote B.V.
 *  All Rights Reserved.
 *
 * NOTICE OF LICENSE
 *
 * All information contained herein is, and remains
 * the property of Cart2Quote B.V. and its suppliers,
 * if any.  The intellectual and technical concepts contained
 * herein are proprietary to Cart2Quote B.V.
 * and its suppliers and may be covered by European and Foreign Patents,
 * patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Cart2Quote B.V.
 *
 * @category    Ophirah
 * @package     Qquoteadv
 * @copyright   Copyright (c) 2015 Cart2Quote B.V. (http://www.cart2quote.com)
 * @license     http://www.cart2quote.com/ordering-licenses
 */
var uploadCount = 0;

/**
 * Enables the browse window for the upload field.
 */
function crmaddon_browseForAttachment(){
    var fileinput = document.getElementById('crm_new_file_'+uploadCount);
    fileinput.click();
}

/**
 * Add a new row in under the upload link
 * @param fileInput
 * @param imagePath
 */
function crmaddon_newAttachment(fileInput, imagePath){
    var ul = $$('#attachment_new UL');
    var li = document.createElement("LI");
    var fileName = fileInput.value.replace(/.*[\/\\]/, '');

    li.innerHTML =
        '<li class="attachment_single">' +
        '<img src="' + imagePath + '" height="14px" width="14px"> ' + fileName + '' +
        '</li>';
    var newFileInput = crmaddon_cloneUploadInput(fileInput);
    var parentDiv = fileInput.parentNode;
    parentDiv.insertBefore(newFileInput, fileInput);
    ul[0].appendChild(li);
}

/**
 * Clones the file input and changing the name
 * @param item
 * @returns Node input
 */
function crmaddon_cloneUploadInput(item){
    var newFileInput = item.clone(true);
    uploadCount++;
    newFileInput.name = 'crm_new_file_'+uploadCount;
    newFileInput.id = newFileInput.name;

    return newFileInput;
}

