

Features of the Orderexporter extension:

1. There are no re-writes of the core in this module. Descreases chances of conflict with other extensions or Magento customizations.
2. Export all orders placed in last hour at the order item level in CSV.
3. Exclude already exported orders from the export.
4. Option to use admin sales order grid filters and override the one hour export filter. This would also allow already exported orders to be exported.This feature allows you to export selected orders using sales order grids in the format/columns you configure for orderexporter CSV.
5. The age in hours for orders that need to be exported is configurable. It is set to 1 hour by default but can be changed from Magento admin's system configurations.
6. Uses core Magento's CSV and grid classes for export.
7. The most powerful feature of the extension - The column fields in the CSV are configurable. The module uses a XML tree to configure the fields in the CSV file. 
8. Any field belonging to sales_flat_order, sales_flat_order_item, sales_flat_order_address, sales_flat_order_payment and product attributes can be configured to be added to the export CSV. 
9. The header to be used for each of these CSV fields can be configured as well.
10. The extension also allows to set the order of occurence of each field in the CSV by using a sort_order node.
11. Ability to upload an XML file for configuring custom columns in orderexport CSV. Users can also view the default configurations in a file downloadable from the admin system configuration. This will allow them to easily configure the custom configuration XML following a similar structure to default config XML.
12. Validation of export CSV configuration XML at the time of upload. Only valid XML configuration will be used for order export.
   

Challenges: 

1. CSV only loads orders placed in last one hour. : Yes.
2. Both parent and line item need to be present.  : Yes, records present at order item level.
3. product sku, price, disocunt, name, billing
   address, shipping address, phone number, are 
   mandaroty field. However other fields can easily
   be added.                                      : Yes. Functionality provided to configure CSV fields on the fly without any code change. 
4. Use Magento given CSV code                     : Yes, core Magento grid and CSV file adapters used for CSV export.
5. CSV should not contain already exported orders : Yes. 

