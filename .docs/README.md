# Configuration

Configure your Calendar component in `config.neon` file.

```neon
# Register Table in decorator to inject the Translation component
decorator:
	JuniWalk\DataTable\Table:
		setup: [setTranslator]

# Include path to the locale file in your Translation component
translation:
	dirs:
		- %vendorDir%/juniwalk/datatable/locale
		- %appDir%/locale
```

## Assets 

Dont forget to include assets in html

- `%vendorDir%/juniwalk/datatable/assets/datatable.js`

Depends on `Naja.js` and `Bootstrap 5` packages.

## First table

Best way to use DataTable is to create custom component extending Table.


```php
namespace App\DataTables;

use JuniWalk\DataTable\Table;
use Nette\Utils\Html;

class FirstTable extends Table
{
	// ? Signal handler for the create toolbar action
	public function handleCreate(): void
	{
		$this->flashMessage('Create signal from Table!', 'success');
		$this->redirect('this');
	}

	// ? Signal handler for the remove row action
	public function handleRemove(int $id): void
	{
		$this->flashMessage('Remove "'.$id.'" signal from Table!', 'danger');
		$this->redirect('this');
	}

	// ? Method returning data to be displayed - it gets piped into SourceFactory
	protected function createModel(): mixed
	{
		return [
			['id' => 1, 'name' => 'John Doe', 'created' => new DateTime('-10 hours')],
			['id' => 2, 'name' => 'Jane Doe', 'created' => new DateTime('-16 hours')],
		];
	}

	// ? Method to create structure of the table
	protected function createTable(): void
	{
		$this->setCaption('My first DataTable');
		$this->setSortable(true);

		// ? Columns
		$this->addColumnText('name', 'Name')->setRenderer($this->columnName(...));
		$this->addColumnDate('created', 'Created');
		$this->addColumnNumber('id', '#');

		// ? Actions
		$this->addActionLink('remove', 'Remove')->setIcon('fa-trash-alt')
			->setConfirmMessage('Are you sure you want to remove "%name%"?')
			->setClass('btn btn-danger btn-xs ajax');

		// ? Toolbar
		$this->addToolbarLink('create', 'Vytvořit')->setIcon('fa-plus')
			->setClass('btn btn-success btn-sm ajax');
	}

	// ? Custom column renderer callback
	private function columnName(array $item, string $value): Html
	{
		return Html::el('strong', $value);
	}
}
```

<!--
# Features

## Basics
- [Columns](Columns.md)
- [Filters](Filters.md)
- [Actions](Actions.md)

## Events
- load
- item
- render
-->
