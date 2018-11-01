<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;

use App\IvaRates;


class IvaController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }
    
    /**
     * Esta función muestra un listado de tipos de IVA creados para la empresa
     * del usuario
     * 
     * @return type
     */
    public function showIvaTypes() {
        
        // compañia del usuario
        $idcomp=Auth::guard('')->user()->idcompany;             
        
        // mensajes
        $messageOK=$messageWrong=null;
                
        try {
            $ivas= IvaRates::where('idcompany',$idcomp)->get();
        } catch (Exception $ex) {
            $ivas=null;
        } catch (QueryException $quex) {
            $ivas=null;            
        }
        
        return view ('company/listIva')
            ->with('ivas',$ivas)
            ->with('company',$idcomp)
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);
    }
    
    
    /**
     * Esta función muestra el formulario para crear o modificar un tipo de IVA.
     * SI es cero id del IVA a modificar, entonces se trata de crear un nuevo
     * IVA.
     * @param type $id
     * @return type
     */
    public function showIva($id=0) {

        // mensajes
        $messageOK=$messageWrong=null;
        
        // compañia del usuario
        $idcomp=Auth::guard('')->user()->idcompany;        
        
        // si el id es cero, entonces se trata de un nuevo iva a grabar
        if ($id==0) {
            
            $iva=new IvaRates;
            
            return view ('company/ivaProfile')
            ->with('iva',$iva)
            ->with('company',$idcomp)                    
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);            
        }
        
        // buscamos el iva a modificar
        $iva= IvaRates::where([
            ['id',$id],
            ['idcompany',$idcomp]
        ])->first();
        
        return view ('company/ivaProfile')
            ->with('iva',$iva)
            ->with('company',$idcomp)                
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);        
        
    }
    
    
    /**
     * Esta función borra un tipo de IVA existente.
     * El borrado se puede realizar desde dos puntos diferentes: desde el listado
     * de Ivas y desde la edición del tipo de IVA.
     * 
     * Se realiza comprobación de que el usuario pertenece a la empresa del IVA
     * que pretende borrar.
     * 
     * @param Request $request
     * @param type $id
     * @return type
     */
    public function deleteIva(Request $request, $id=0) {
        
        // mensajes
        $messageOK=$messageWrong=null;        
        
        // compañia del usuario
        $idcomp=Auth::guard('')->user()->idcompany;             

        // por seguridad verificamos si el usuario pertenece a la empresa
        $company= clearInput($request->input('companyid'));
        
        if ($idcomp==$company) {
            // todo OK
            
            if ($id==0) {
                // si no hemos entrado desde listado sino desde edicion de IVA
                // obtenemos el tipo de iva correspondiente al id y a la empresa
                // del usuario
                $id= clearInput($request->input('ivaid'));

                try {
                    $iva= IvaRates::where([
                        ['id',$id],
                        ['idcompany',$idcomp]
                    ])->first();
                    if (is_null($iva) || $iva===false) {
                        // no encontrado el iva  eliminar
                        $messageWrong='El IVA que intenta eliminar no existe';
                    } else {
                        $iva->delete();
                        $messageOK='IVA eliminado definitivamente';
                        // desde aquí se retorna a la lista
                    }
                } catch (Exception $ex) {
                    $messageWrong='Error intentando eliminar un tipo de IVA';
                    // desde aquí se vuelve a edición
                    // buscamos el iva a modificar
                    $iva= IvaRates::where([
                        ['id',$id],
                        ['idcompany',$idcomp]
                    ])->first();

                    return view ('company/ivaProfile')
                        ->with('iva',$iva)
                        ->with('company',$idcomp)                            
                        ->with('messageOK',$messageOK)
                        ->with('messageWrong',$messageWrong);    
                    
                } catch (QueryException $quex) {
                    $messageWrong='Error en base de datos intentando eliminar un tipo de IVA'; 
                    // desde aquí se vuelve a edición
                    // buscamos el iva a modificar
                    $iva= IvaRates::where([
                        ['id',$id],
                        ['idcompany',$idcomp]
                    ])->first();

                    return view ('company/ivaProfile')
                        ->with('iva',$iva)
                        ->with('company',$idcomp)                            
                        ->with('messageOK',$messageOK)
                        ->with('messageWrong',$messageWrong);                    
                }            
            } else {
                // desde listado
                // obtenemos el tipo de iva correspondiente al id y a la empresa
                // del usuario
                try {
                    $iva= IvaRates::where([
                        ['id',$id],
                        ['idcompany',$idcomp]
                    ])->first();
                    if (is_null($iva) || $iva===false) {
                        // no encontrado el iva  eliminar
                        $messageWrong='El IVA que intenta eliminar no existe';
                    } else {
                        $iva->delete();
                        $messageOK='IVA eliminado definitivamente';
                    }
                } catch (Exception $ex) {
                    $messageWrong='Error intentando eliminar un tipo de IVA';
                } catch (QueryException $quex) {
                    $messageWrong='Error en base de datos intentando eliminar un tipo de IVA';           
                }          

            }            
            
        } else {
            $messageWrong='Usuario no pertenece a la empresa del IVA a borrar';
        }
                  
        try {
            $ivas= IvaRates::where('idcompany',$idcomp)->get();
        } catch (Exception $ex) {
            $ivas=null;
        } catch (QueryException $quex) {
            $ivas=null;            
        }
        
        return view ('company/listIva')
            ->with('ivas',$ivas)
            ->with('company',$idcomp)                
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);
        
    }
    
    
    /**
     * Esta función graba en base de datos un nuevo tipo de IVA, con los datos
     * del formulario.
     * 
     * Se realiza comprobación de que el usuario pertenece a la empresa del IVA
     * que pretende grabar
     * 
     * @param Request $request
     * @return type
     */
    public function recordNewIva(Request $request) {
        
        // mensajes
        $messageOK=$messageWrong=null; 
        
        // compañia del usuario
        $idcomp=Auth::guard('')->user()->idcompany;             

        $company= clearInput($request->input('companyid'));

        // por seguridad verificamos si el usuario pertenece a la empresa        
        if ($idcomp==$company) {
            
            // leemos el formulario
            $name= clearInput($request->input('ivaname'));
            $rate= clearInput($request->input('ivarate'));
            $type= clearInput($request->input('ivatype'));
            $active= clearInput($request->input('ivaactive'));
                        
            // verificamos datos
            $error=false;
            
            // depuramos entradas
            if (strlen($name)<3 || strlen($name)>100) {
                $messageW=' - Concepto de IVA con longitud inadecuada.';
                $error=true;
            }
            if (!is_numeric($rate) || $rate>100 || $rate<0) {                
                $messageW=' - Porcentaje de IVA incorrecto.';
                $error=true;
            } 
            if (!is_numeric($type) || $type>3 || $type<0) {                
                $messageW=' - Tipo de IVA incorrecto.';
                $error=true;
            }             
            ($active=='OFF') ? $active=1 : $active=0;
            
            // si hay errores en la comprobación no procedemos a grabar
            if ($error===true) {
                $messageWrong='Datos incorrectos en el formulario:<br />'.$messageW;    
            } else {
                // creamos el objeto iva
                $iva=new IvaRates;
                $iva->idcompany=$idcomp;
                $iva->iva_name=$name;
                $iva->rate=$rate;
                $iva->type=$type;
                $iva->active=$active;
                
                try {
                    // grabamos
                    $iva->save();
                
                    // mensaje
                    $messageOK='Nuevo tipo de IVA creado correctamente';
                } catch (Exception $ex) {
                    $messageWrong='Error intentando grabar un nuevo tipo de IVA';
                } catch (QueryException $quex) {
                    $messageWrong='Error en base de datos intentando grabar un nuevo tipo de IVA';             
                }                
               
            }

        } else {
            $iva=new IvaRates;
            $messageWrong='Usuario no pertenece a la empresa del IVA a grabar';
        } 
        
        // mostramos el iva recien creado
        return view ('company/ivaProfile')
            ->with('iva',$iva)
            ->with('company',$idcomp)                
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong); 
        
    }
    
    
    /**
     * Esta función modifica en base de datos un tipo de IVA existente, con los datos
     * del formulario.
     * 
     * Se realiza comprobación de que el usuario pertenece a la empresa del IVA
     * que pretende grabar
     * 
     * @param Request $request
     * @return type
     */
    public function updateIva(Request $request) {
        
        // mensajes
        $messageOK=$messageWrong=null; 
        
        // compañia del usuario
        $idcomp=Auth::guard('')->user()->idcompany;             

        $company= clearInput($request->input('companyid'));

        // por seguridad verificamos si el usuario pertenece a la empresa        
        if ($idcomp==$company) {
            
            // leemos el formulario
            $id= clearInput($request->input('ivaid'));            
            $name= clearInput($request->input('ivaname'));
            $rate= clearInput($request->input('ivarate'));
            $type= clearInput($request->input('ivatype'));
            $active= clearInput($request->input('ivaactive'));
                        
            // verificamos datos
            $error=false;
            
            // depuramos entradas
            if (strlen($name)<3 || strlen($name)>100) {
                $messageW=' - Concepto de IVA con longitud inadecuada.';
                $error=true;
            }
            if (!is_numeric($rate) || $rate>100 || $rate<0) {                
                $messageW=' - Porcentaje de IVA incorrecto.';
                $error=true;
            } 
            if (!is_numeric($type) || $type>3 || $type<0) {                
                $messageW=' - Tipo de IVA incorrecto.';
                $error=true;
            }            
            //die ('***'.$active);
            ($active=='OFF' || $active=='ON') ? $active=1 : $active=0;
            
            // si hay errores en la comprobación no procedemos a grabar
            if ($error===true) {
                $messageWrong='Datos incorrectos en el formulario:<br />'.$messageW;    
            } else {
                
                try {
                    // obtenemos el objeto iva
                    $iva= IvaRates::where([
                            ['id',$id],
                            ['idcompany',$idcomp]
                        ])->first();
                    $iva->idcompany=$idcomp;
                    $iva->iva_name=$name;
                    $iva->rate=$rate;
                    $iva->type=$type;
                    $iva->active=$active;                    
                    // grabamos
                    $iva->save();
                
                    // mensaje
                    $messageOK='El tipo de IVA ha sido modificado correctamente';
                } catch (Exception $ex) {
                    $messageWrong='Error intentando modificar un tipo de IVA';
                } catch (QueryException $quex) {
                    $messageWrong='Error en base de datos intentando modificar un tipo de IVA';             
                }                
               
            }

        } else {
            $iva=new IvaRates;
            $messageWrong='Usuario no pertenece a la empresa del IVA a modificar';
        } 
        
        // mostramos el iva recien creado
        return view ('company/ivaProfile')
            ->with('iva',$iva)
            ->with('company',$idcomp)                
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong); 
        
    }    
    
    
}
