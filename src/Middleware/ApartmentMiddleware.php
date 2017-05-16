<?php

namespace BuildEmpire\Apartment\Middleware;

use Closure;
use BuildEmpire\Apartment\Schema;

class ApartmentMiddleware
{
    protected $apartmentSchema;

    public function __construct(Schema $apartmentSchema)
    {
        $this->apartmentSchema = $apartmentSchema;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $subdomain = explode('.', $request->getHost())[0];
        if ($this->apartmentSchema->doesSchemaExist($subdomain)) {
            $this->apartmentSchema->trySetSchemaName($subdomain);
        }
        return $next($request);
    }

}