let dataGridTranslations = {
    // Root
    noRowsLabel: 'بدون ردیف',
    noResultsOverlayLabel: 'نتیجه ای یافت نشد',
    errorOverlayDefaultLabel: 'یک خطا رخ داد',

    // Density selector toolbar button text
    toolbarDensity: 'غلظت',
    toolbarDensityLabel: 'غلظت',
    toolbarDensityCompact: 'فشرده',
    toolbarDensityStandard: 'استاندارد',
    toolbarDensityComfortable: 'گشوده',

    // Columns selector toolbar button text
    toolbarColumns: 'ستون ها',
    toolbarColumnsLabel: 'ستون ها را انتخاب کنید',

    // Filters toolbar button text
    toolbarFilters: 'فیلتر ها',
    toolbarFiltersLabel: 'نمایش فیلتر ها',
    toolbarFiltersTooltipHide: 'پنهان کردن فیلتر ها',
    toolbarFiltersTooltipShow: 'نمایش فیلتر ها',
    toolbarFiltersTooltipActive: (count) => `فیلتر فعال ${count}`,

    // Quick filter toolbar field
    toolbarQuickFilterPlaceholder: 'جست و جو...',
    toolbarQuickFilterLabel: 'جست و جو',
    toolbarQuickFilterDeleteIconLabel: 'پنهان',

    // Export selector toolbar button text
    toolbarExport: 'ترخیص',
    toolbarExportLabel: 'ترخیص',
    toolbarExportCSV: 'دانلود ورژن CSV',
    toolbarExportPrint: 'پرینت',
    toolbarExportExcel: 'دانلود ورژن Excel',

    // Columns panel text
    columnsPanelTextFieldLabel: 'ستون را پیدا کن',
    columnsPanelTextFieldPlaceholder: 'عنوان ستون',
    columnsPanelDragIconLabel: 'مجدد ستون را مرتب کن',
    columnsPanelShowAllButton: 'همه را نمایش بده',
    columnsPanelHideAllButton: 'همه را پنهان کن',

    // Filter panel text
    filterPanelAddFilter: 'فیلتر اضافه کن',
    filterPanelDeleteIconLabel: 'حذف کن',
    filterPanelLinkOperator: 'عملگر منطقی',
    filterPanelOperator: 'عملگر', // TODO v6: rename to filterPanelOperator
    filterPanelOperators: 'عملگر', // TODO v6: rename to filterPanelOperator
    filterPanelOperatorAnd: 'و',
    filterPanelOperatorOr: 'یا',
    filterPanelColumns: 'ستون ها',
    filterPanelInputLabel: 'مقدار',
    filterPanelInputPlaceholder: 'مقدار فیلتر',

    // Filter operators text
    filterOperatorContains: 'شامل می شود',
    filterOperatorEquals: 'برابر است با',
    filterOperatorStartsWith: 'شروع می شود',
    filterOperatorEndsWith: 'پایان می یابد',
    filterOperatorIs: 'هست',
    filterOperatorNot: 'نیست',
    filterOperatorAfter: 'هست بعد از',
    filterOperatorOnOrAfter: 'هست در یا بعد از',
    filterOperatorBefore: 'هست قبل از',
    filterOperatorOnOrBefore: 'هست در یا قبل از',
    filterOperatorIsEmpty: 'هست خالی',
    filterOperatorIsNotEmpty: 'نیست خالی',
    filterOperatorIsAnyOf: 'هست یکی از',

    // Filter values text
    filterValueAny: 'هر کدام',
    filterValueTrue: 'صحیح',
    filterValueFalse: 'غلط',

    // Column menu text
    columnMenuLabel: 'منو',
    columnMenuShowColumns: 'نمایش ستون ها',
    columnMenuFilter: 'فیلتر کردن',
    columnMenuHideColumn: 'پنهان کردن',
    columnMenuUnsort: 'نامرتب کردن',
    columnMenuSortAsc: 'افزاینده بچین',
    columnMenuSortDesc: 'کاهنده بچین',

    // Column header text
    columnHeaderFiltersTooltipActive: (count) => `فیلتر فعال ${count}`,
    columnHeaderFiltersLabel: 'نمایش فیلتر ها',
    columnHeaderSortIconLabel: 'مرتب کن',

    // Rows selected footer text
    footerRowSelected: (count) => `${count.toLocaleString()} ردیف انتخاب شد`,

    // Total row amount footer text
    footerTotalRows: 'مجموع ردیف ها:',

    // Total visible row amount footer text
    footerTotalVisibleRows: (visibleCount, totalCount) => `${totalCount.toLocaleString()} از ${visibleCount.toLocaleString()}`,

    // Checkbox selection text
    checkboxSelectionHeaderName: 'گزینش',
    checkboxSelectionSelectAllRows: 'همه ی ردیف ها را انتخاب کن',
    checkboxSelectionUnselectAllRows: 'همه ی ردیف ها را انتخاب نکن',
    checkboxSelectionSelectRow: 'ردیف را انتخاب کن',
    checkboxSelectionUnselectRow: 'ردیف را انتخاب نکن',

    // Boolean cell text
    booleanCellTrueLabel: 'بله',
    booleanCellFalseLabel: 'نه',

    // Actions cell more text
    actionsCellMore: 'بیشتر',

    // Column pinning text
    pinToLeft: 'بچسبات به چپ',
    pinToRight: 'بچسبان به راست',
    unpin: 'جدا کن',

    // Tree Data
    treeDataGroupingHeaderName: 'گروه',
    treeDataExpand: 'زیرگروه ها را ببین',
    treeDataCollapse: 'زیرگروه ها را پنهان کن',

    // Grouping columns
    groupingColumnHeaderName: 'گروه',
    groupColumn: (name) => `گروه بندی کن ${name} بر اساس`,
    unGroupColumn: (name) => `را متوقف کن ${name} گروه بندی بر اساس`,

    // Master/detail
    detailPanelToggle: 'تغییر پنل نمایش',
    expandDetailPanel: 'بگشا',
    collapseDetailPanel: 'جمع کن',

    // Used core components translation keys
    MuiTablePagination: {},

    // Row reordering text
    rowReorderingHeaderName: 'چیدن جدید ردیف ها',

    // Aggregation
    aggregationMenuItemHeader: 'نحوه جمع آوری',
    aggregationFunctionLabelSum: 'مجموع',
    aggregationFunctionLabelAvg: 'میانگین',
    aggregationFunctionLabelMin: 'مینیمام',
    aggregationFunctionLabelMax: 'ماکسیمام',
    aggregationFunctionLabelSize: 'اندازه',
};

export { dataGridTranslations };
