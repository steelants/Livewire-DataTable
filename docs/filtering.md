# Filters methods
``` php
    //Add filters to header for specific columns
    public function headerFilters(): array
    {
        return [
            'column1Key' => ['type' => 'text'], //input type
            'column2Key' => ['type' => 'select', 'values' => ['value' => 'name', 'value2' => 'name2']], //this for select
            'column3Key' => ['type' => 'date'], //double input type (date,time,datetime-local)
        ];
    }

    //Add actions to header filters edit
    public function updatedHeaderFilter(){
        $this->validate([
            'headerFilter.column1Key' => 'nullable|string',
            'headerFilter.column2Key' => 'nullable|string',
            'headerFilter.column3Key.*' => 'nullable|date', //have two parameters "from" and "to"
        ]);
    }
```
