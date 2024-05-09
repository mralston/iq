<?php
 
namespace Mralston\Iq\Models\Scopes;
 
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
 
class TariffScope implements Scope
{
	public function apply(Builder $builder, Model $model): void
	{
		$builder->where('Type', 'TARIFFS');
	}
}