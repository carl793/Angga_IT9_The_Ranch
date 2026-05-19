# Requirements Document: IT9 System Optimization & Feature Expansion

## Introduction

This document specifies the requirements for optimizing and expanding The Ranch Farm Inventory Management System. The system manages farm inventory using a Product Blueprint vs Physical Stock paradigm where Products represent item templates and Batches represent physical inventory instances with expiry tracking. The optimization focuses on four key areas: navigation consolidation, intelligent stock routing with FEFO logic, enhanced dashboard analytics, and visual product catalog with quick actions.

## Glossary

- **System**: The Ranch Farm Inventory Management System (Laravel-based web application)
- **Product**: A blueprint/template representing an item type (e.g., "Organic Tomatoes")
- **Batch**: A physical inventory instance of a Product with specific quantity, cost, and expiry date
- **Stock_In**: The operation of receiving new inventory and creating a Batch
- **Stock_Out**: The operation of deducting inventory from one or more Batches
- **FEFO**: First-Expired, First-Out inventory routing strategy
- **Active_Batch**: A Batch with current_quantity greater than zero
- **Stock_Movement**: An audit trail record of inventory changes (in/out/adjustment)
- **User**: An authenticated system user with role (Admin, Manager, or Staff)
- **Dashboard**: The main analytics and alerts screen shown after login
- **Product_Management_View**: The consolidated interface for Products, Categories, and Units
- **Gallery_View**: A visual card-based interface displaying Products with images
- **Product_Profile**: A detailed view of a single Product with aggregated stock information
- **Expiry_Forecast**: A time-based projection of Batches approaching expiry
- **Inventory_Valuation**: The total monetary value of all Active_Batches (sum of current_quantity × cost_price)
- **Sub_Navigation**: A horizontal tab-based navigation within a specific view
- **Quick_Action**: A streamlined operation triggered directly from the Gallery_View or Product_Profile

## Requirements

### Requirement 1: Navigation Consolidation

**User Story:** As a system user, I want a streamlined sidebar navigation, so that I can focus on primary business modules without visual clutter.

#### Acceptance Criteria

1. THE System SHALL rename the "Products" sidebar menu item to "Product Management"
2. THE System SHALL remove "Categories" from the main sidebar navigation
3. THE System SHALL remove "Units of Measure" from the main sidebar navigation
4. WHEN a User navigates to "Product Management", THE System SHALL display a horizontal Sub_Navigation containing three tabs: "Products", "Categories", and "Units"
5. WHEN a User clicks a Sub_Navigation tab, THE System SHALL display the corresponding content without full page reload
6. THE System SHALL preserve role-based access control for Categories and Units within the Sub_Navigation
7. THE System SHALL maintain the current URL routing structure for direct access to Products, Categories, and Units

### Requirement 2: FEFO Stock Routing Engine

**User Story:** As a warehouse operator, I want the system to automatically select batches based on expiry dates, so that I can prevent waste and ensure product freshness.

#### Acceptance Criteria

1. WHEN a User submits a Stock_Out request with a Product and quantity, THE System SHALL query all Active_Batches for that Product ordered by expiry_date ascending
2. THE System SHALL deduct the requested quantity from the Active_Batch with the earliest expiry_date first
3. IF the requested quantity exceeds the current_quantity of the earliest expiring Active_Batch, THEN THE System SHALL deduct the remaining quantity from the next earliest expiring Active_Batch
4. THE System SHALL continue cascading deductions across Active_Batches until the requested quantity is fulfilled or Active_Batches are exhausted
5. IF the total available quantity across all Active_Batches is less than the requested quantity, THEN THE System SHALL return an error message indicating insufficient stock
6. THE System SHALL create a Stock_Movement record for each Active_Batch that was deducted from
7. THE System SHALL update the current_quantity for each affected Active_Batch atomically within a database transaction
8. WHEN multiple Active_Batches have identical expiry_dates, THE System SHALL deduct from the Batch with the lowest batch_id first
9. THE System SHALL preserve the audit trail by recording the User, timestamp, quantity, and reason for each Stock_Movement
10. FOR ALL valid Stock_Out operations, the sum of quantities deducted SHALL equal the requested quantity (invariant property)
11. FOR ALL Stock_Out operations, the total stock before operation minus requested quantity SHALL equal total stock after operation (conservation property)

### Requirement 3: Enhanced Dashboard Analytics

**User Story:** As a farm manager, I want comprehensive analytics on my dashboard, so that I can make informed inventory decisions.

#### Acceptance Criteria

1. THE Dashboard SHALL display a line chart showing monthly Stock_In volume for the past 12 months
2. THE Dashboard SHALL display a line chart showing monthly Stock_Out volume for the past 12 months
3. THE Dashboard SHALL calculate and display the Inventory_Valuation as a monetary amount
4. THE Inventory_Valuation SHALL be computed as the sum of (current_quantity × cost_price) for all Active_Batches
5. THE Dashboard SHALL display an "Expiring in 30 Days" widget showing Batches expiring within 30 days
6. THE Dashboard SHALL display an "Expiring in 60 Days" widget showing Batches expiring within 31-60 days
7. THE Dashboard SHALL display an "Expiring in 90 Days" widget showing Batches expiring within 61-90 days
8. WHEN a User clicks on an expiry forecast widget, THE System SHALL display a detailed list of affected Batches with Product names, quantities, and exact expiry dates
9. THE Dashboard SHALL refresh analytics data on each page load
10. THE System SHALL exclude Batches with current_quantity of zero from all Dashboard calculations
11. FOR ALL Inventory_Valuation calculations, the result SHALL be non-negative (invariant property)
12. FOR ALL expiry forecast widgets, Batches SHALL appear in exactly one time window (mutual exclusivity property)

### Requirement 4: Visual Product Catalog with Image Support

**User Story:** As a warehouse operator, I want to see product images in a visual catalog, so that I can quickly identify items without relying solely on text descriptions.

#### Acceptance Criteria

1. THE System SHALL add an image_path column to the products table to store image file paths
2. THE System SHALL use Laravel's local storage driver to store uploaded product images
3. WHEN a User creates or edits a Product, THE System SHALL provide an image upload field
4. THE System SHALL validate uploaded images to ensure they are in JPEG, PNG, or WebP format
5. THE System SHALL validate uploaded images to ensure file size does not exceed 5MB
6. THE System SHALL store uploaded images in the storage/app/public/products directory
7. THE System SHALL generate a unique filename for each uploaded image to prevent collisions
8. IF a Product does not have an image_path, THEN THE System SHALL display a default placeholder image
9. THE System SHALL create a Gallery_View route accessible from the main navigation
10. THE Gallery_View SHALL display Products as image cards in a responsive grid layout
11. WHEN a User views the Gallery_View, THE System SHALL display each Product's image, name, SKU, and total aggregated quantity
12. THE total aggregated quantity SHALL be calculated as the sum of current_quantity across all Active_Batches for that Product
13. WHEN a User clicks a Product card in the Gallery_View, THE System SHALL open a Product_Profile modal or page
14. THE Product_Profile SHALL display the Product image, name, SKU, category, unit, total quantity, and list of Active_Batches
15. THE Product_Profile SHALL provide a Quick_Action button for Stock_In
16. THE Product_Profile SHALL provide a Quick_Action button for Stock_Out
17. WHEN a User triggers a Quick_Action for Stock_In, THE System SHALL pre-populate the Product field and display the Stock_In form
18. WHEN a User triggers a Quick_Action for Stock_Out, THE System SHALL use the FEFO routing logic to automatically select Batches
19. THE System SHALL maintain referential integrity by preventing deletion of Products that have Active_Batches
20. FOR ALL image uploads, the stored file SHALL be readable and displayable (round-trip property)
21. FOR ALL Products, the aggregated quantity in Gallery_View SHALL equal the sum of Batch quantities (consistency property)

### Requirement 5: Image Upload Parser and Validator

**User Story:** As a developer, I want robust image upload handling, so that the system reliably processes and stores product images.

#### Acceptance Criteria

1. THE Image_Upload_Handler SHALL parse multipart/form-data requests containing image files
2. THE Image_Upload_Handler SHALL validate the MIME type against allowed types (image/jpeg, image/png, image/webp)
3. THE Image_Upload_Handler SHALL validate the file size does not exceed 5242880 bytes (5MB)
4. IF validation fails, THEN THE Image_Upload_Handler SHALL return a descriptive error message
5. THE Image_Upload_Handler SHALL generate a unique filename using the pattern: {timestamp}_{random_string}.{extension}
6. THE Image_Upload_Handler SHALL store the file using Laravel's Storage facade
7. THE Image_Upload_Handler SHALL return the relative storage path for database storage
8. WHEN a Product with an existing image is updated with a new image, THE System SHALL delete the old image file
9. THE System SHALL provide an Image_Retrieval_Service to generate public URLs for stored images
10. FOR ALL successfully uploaded images, retrieving and re-uploading the image SHALL produce a valid stored file (round-trip property)
11. FOR ALL image operations, the file system state SHALL remain consistent with database records (consistency property)

### Requirement 6: Stock Movement Audit Trail Integrity

**User Story:** As an auditor, I want complete and accurate stock movement records, so that I can trace all inventory changes.

#### Acceptance Criteria

1. THE System SHALL create a Stock_Movement record for every Stock_In operation
2. THE System SHALL create a Stock_Movement record for every Stock_Out operation
3. THE System SHALL create a Stock_Movement record for every manual adjustment operation
4. WHEN a Stock_Out operation affects multiple Batches, THE System SHALL create one Stock_Movement record per affected Batch
5. THE Stock_Movement SHALL record the batch_id, user_id, type, quantity, reason, and timestamp
6. THE System SHALL prevent deletion of Stock_Movement records
7. THE System SHALL prevent modification of Stock_Movement records after creation
8. FOR ALL Batches, the current_quantity SHALL equal initial_quantity plus sum of Stock_In movements minus sum of Stock_Out movements (conservation property)
9. FOR ALL Stock_Movement records, the associated Batch and User SHALL exist in the database (referential integrity property)

### Requirement 7: Role-Based Access Control Preservation

**User Story:** As a system administrator, I want role-based access controls maintained across all new features, so that security policies remain enforced.

#### Acceptance Criteria

1. THE System SHALL restrict access to Categories management within Product_Management_View to Manager and Admin roles
2. THE System SHALL restrict access to Units management within Product_Management_View to Manager and Admin roles
3. THE System SHALL allow all authenticated Users to view the Gallery_View
4. THE System SHALL allow all authenticated Users to view Product_Profile pages
5. THE System SHALL restrict Stock_In Quick_Actions to Manager and Admin roles
6. THE System SHALL allow all authenticated Users to perform Stock_Out Quick_Actions
7. THE System SHALL restrict image upload functionality to Manager and Admin roles
8. THE System SHALL restrict image deletion functionality to Manager and Admin roles
9. IF a User attempts an unauthorized action, THEN THE System SHALL return a 403 Forbidden response
10. FOR ALL protected routes, unauthorized access attempts SHALL be denied (security invariant)

### Requirement 8: Database Migration and Data Integrity

**User Story:** As a database administrator, I want safe schema migrations, so that existing data remains intact during system upgrades.

#### Acceptance Criteria

1. THE System SHALL provide a database migration to add the image_path column to the products table
2. THE image_path column SHALL be nullable to support existing Products without images
3. THE migration SHALL execute without data loss on existing products, batches, and stock_movements tables
4. THE System SHALL maintain all foreign key constraints during migration
5. THE System SHALL provide a rollback migration to remove the image_path column
6. WHEN the rollback migration executes, THE System SHALL delete all stored product images from the file system
7. FOR ALL migrations, the database schema SHALL remain consistent with model definitions (schema consistency property)
8. FOR ALL migrations, existing data relationships SHALL be preserved (referential integrity property)

### Requirement 9: Performance and Scalability

**User Story:** As a system user, I want fast page loads and responsive interactions, so that I can work efficiently.

#### Acceptance Criteria

1. THE Gallery_View SHALL load and render within 2 seconds for up to 500 Products
2. THE Dashboard analytics calculations SHALL complete within 3 seconds for up to 10,000 Stock_Movements
3. THE FEFO routing algorithm SHALL execute within 500 milliseconds for up to 100 Active_Batches per Product
4. THE System SHALL use eager loading to prevent N+1 query problems when displaying Products with Batches
5. THE System SHALL cache Dashboard analytics data for 5 minutes to reduce database load
6. THE System SHALL use database indexes on expiry_date and current_quantity columns for efficient querying
7. THE System SHALL paginate the Gallery_View when displaying more than 50 Products
8. THE System SHALL optimize image delivery by serving images through Laravel's storage link

### Requirement 10: Error Handling and User Feedback

**User Story:** As a system user, I want clear error messages and success confirmations, so that I understand the results of my actions.

#### Acceptance Criteria

1. WHEN a Stock_Out operation fails due to insufficient stock, THE System SHALL display the requested quantity and available quantity
2. WHEN an image upload fails validation, THE System SHALL display the specific validation error (file type, file size, etc.)
3. WHEN a FEFO routing operation succeeds, THE System SHALL display a success message listing all affected Batches
4. WHEN a database transaction fails, THE System SHALL roll back all changes and display a generic error message
5. WHEN a User attempts to delete a Product with Active_Batches, THE System SHALL display an error message indicating the constraint violation
6. THE System SHALL log all errors to the Laravel log file with sufficient context for debugging
7. THE System SHALL display validation errors inline next to the relevant form fields
8. THE System SHALL preserve form input data when validation fails to prevent data loss

## Implementation Notes

### Parser and Serializer Requirements

This feature includes image upload handling which requires careful parsing and validation:

- **Image Upload Parser**: Parses multipart/form-data containing image files
- **Image Validator**: Validates MIME type, file size, and format
- **Storage Path Serializer**: Generates and stores relative file paths
- **Image Retrieval Service**: Converts storage paths to public URLs

**Round-Trip Property**: For all successfully uploaded images, the following must hold:
```
upload(image) → store(path) → retrieve(path) → display(image)
```

The displayed image must be visually identical to the uploaded image (allowing for format conversion).

### FEFO Algorithm Correctness Properties

The FEFO routing algorithm must satisfy these properties:

1. **Conservation**: Total stock before - requested quantity = Total stock after
2. **Ordering**: Batches are deducted in strict expiry_date ascending order
3. **Completeness**: All requested quantity is deducted OR insufficient stock error is raised
4. **Atomicity**: Either all deductions succeed or none succeed (transaction boundary)
5. **Audit Trail**: One Stock_Movement per affected Batch with correct quantities

### Dashboard Analytics Correctness Properties

Dashboard calculations must satisfy:

1. **Non-negativity**: Inventory_Valuation ≥ 0
2. **Mutual Exclusivity**: Each Batch appears in exactly one expiry forecast window
3. **Completeness**: All Active_Batches appear in some expiry forecast window OR are beyond 90 days
4. **Consistency**: Sum of Stock_In volumes = Sum of all Stock_In Stock_Movements for the period

### Data Integrity Invariants

The following invariants must hold at all times:

1. **Batch Quantity**: `batch.current_quantity = batch.initial_quantity + sum(in_movements) - sum(out_movements)`
2. **Product Stock**: `product.totalStock() = sum(batch.current_quantity for all batches of product)`
3. **Active Batch**: `batch is Active_Batch ⟺ batch.current_quantity > 0`
4. **Image Path**: `product.image_path IS NULL OR file_exists(storage_path(product.image_path))`

## Acceptance Testing Strategy

### Property-Based Testing Candidates

The following requirements are excellent candidates for property-based testing:

1. **FEFO Routing (Req 2)**: Generate random Product configurations with multiple Batches and random Stock_Out quantities. Verify conservation, ordering, and completeness properties hold for all inputs.

2. **Inventory Valuation (Req 3)**: Generate random Batch configurations with varying quantities and prices. Verify non-negativity and correct calculation for all inputs.

3. **Image Upload Round-Trip (Req 5)**: Generate random valid images in different formats and sizes. Verify upload → store → retrieve → display produces valid images.

4. **Stock Movement Audit Trail (Req 6)**: Generate random sequences of Stock_In and Stock_Out operations. Verify Batch quantities match audit trail calculations for all sequences.

### Integration Testing Candidates

The following requirements require integration tests with representative examples:

1. **Navigation Consolidation (Req 1)**: Test with 2-3 user roles to verify Sub_Navigation rendering and access control.

2. **Dashboard Analytics (Req 3)**: Test with sample datasets representing typical farm inventory scenarios (small, medium, large).

3. **Gallery View (Req 4)**: Test with Products with/without images, varying quantities, and different screen sizes.

4. **Role-Based Access Control (Req 7)**: Test each role against each protected route with 1-2 examples per combination.

5. **Database Migration (Req 8)**: Test migration up/down with sample data to verify data preservation.

6. **Performance (Req 9)**: Test with representative dataset sizes (100, 500, 1000 Products) to verify performance targets.

7. **Error Handling (Req 10)**: Test each error condition with 1-2 representative examples.

## Traceability Matrix

| Requirement | Phase | Priority | Testing Strategy |
|-------------|-------|----------|------------------|
| Req 1: Navigation Consolidation | Phase 1 | High | Integration (2-3 examples) |
| Req 2: FEFO Stock Routing | Phase 2 | Critical | Property-Based + Integration |
| Req 3: Enhanced Dashboard | Phase 3 | High | Property-Based + Integration |
| Req 4: Visual Product Catalog | Phase 4 | High | Integration (multiple scenarios) |
| Req 5: Image Upload Handler | Phase 4 | High | Property-Based (round-trip) |
| Req 6: Audit Trail Integrity | Phase 2 | Critical | Property-Based (conservation) |
| Req 7: Role-Based Access | All Phases | Critical | Integration (role matrix) |
| Req 8: Database Migration | Phase 4 | High | Integration (up/down) |
| Req 9: Performance | All Phases | Medium | Integration (load testing) |
| Req 10: Error Handling | All Phases | High | Integration (error scenarios) |

## Constraints and Assumptions

### Constraints

1. The system MUST maintain backward compatibility with existing Product, Batch, and Stock_Movement data
2. The system MUST NOT modify existing functional code unless required for feature implementation
3. The system MUST preserve all existing role-based access control policies
4. The system MUST maintain referential integrity across all database operations
5. The system MUST use Laravel's built-in Storage facade for file operations

### Assumptions

1. The system assumes all Users have modern web browsers supporting HTML5 and CSS3
2. The system assumes the server has sufficient disk space for product images (estimated 10MB per 100 products)
3. The system assumes the database supports transactions (MySQL/PostgreSQL/SQLite with appropriate configuration)
4. The system assumes all Batches have valid expiry_date values for FEFO routing
5. The system assumes network latency is reasonable (<500ms) for image uploads

## Success Criteria

The feature is considered successfully implemented when:

1. All 10 requirements have passing tests (property-based and integration)
2. The system passes manual UAT with farm managers and warehouse operators
3. Performance benchmarks meet or exceed specified targets (Req 9)
4. All existing functionality remains operational (regression testing passes)
5. Code review confirms adherence to Laravel best practices and project coding standards
6. Documentation is complete (user guide, API documentation, deployment guide)
