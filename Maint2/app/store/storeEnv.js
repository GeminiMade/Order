/*
 * File: app/store/storeEnv.js
 *
 * This file was generated by Sencha Architect version 4.2.6.
 * http://www.sencha.com/products/architect/
 *
 * This file requires use of the Ext JS 6.7.x Classic library, under independent license.
 * License of Sencha Architect does not include license for Ext JS 6.7.x Classic. For more
 * details see http://www.sencha.com/license or contact license@sencha.com.
 *
 * This file will be auto-generated each and everytime you save your project.
 *
 * Do NOT hand edit this file.
 */

Ext.define('MyApp.store.storeEnv', {
    extend: 'Ext.data.Store',

    requires: [
        'Ext.data.field.Field'
    ],

    constructor: function(cfg) {
        var me = this;
        cfg = cfg || {};
        me.callParent([Ext.apply({
            storeId: 'storeEnv',
            data: [
                {
                    id: 'GI',
                    text: 'Production (GI)'
                },
                {
                    id: 'GT',
                    text: 'Testing/Training (GT)'
                },
                {
                    id: 'TS',
                    text: 'Gemini Development (TS)'
                }
            ],
            fields: [
                {
                    name: 'id'
                },
                {
                    name: 'text'
                }
            ]
        }, cfg)]);
    }
});