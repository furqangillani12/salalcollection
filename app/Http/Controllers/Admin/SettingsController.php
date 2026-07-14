<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\DispatchMethod;
use App\Models\DeliveryChargeSlab;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /** Keys managed by the Website / Social settings card. */
    public const SITE_KEYS = [
        'site_phone', 'site_whatsapp', 'site_email', 'site_address',
        'social_facebook', 'social_instagram', 'social_whatsapp',
        'social_tiktok', 'social_x', 'social_youtube',
        'shop_tax_rate', 'shop_tax_type',
        'notice_title', 'notice_short', 'notice_full',
        'site_name', 'site_name_ur', 'site_website', 'dispatch_postman_note', 'dispatch_postman_note_ur',
        'topbar_text', 'topbar_location',
        'dispatch_logo_en', 'dispatch_logo_ur', 'dispatch_slip_lang',
        'points_rupees_per_point', 'points_per_review', 'points_value_rupees',
    ];

    public function index()
    {
        $paymentMethods = PaymentMethod::orderBy('sort_order')->get();
        $dispatchMethods = DispatchMethod::with('deliverySlabs')->orderBy('sort_order')->get();
        $deliverySlabs = DeliveryChargeSlab::with('dispatchMethod')->orderBy('dispatch_method_id')->orderBy('min_weight')->get();
        $site = Setting::allValues();

        return view('admin.settings.index', compact('paymentMethods', 'dispatchMethods', 'deliverySlabs', 'site'));
    }

    // ── Website / Social settings ──

    public function updateSiteSettings(Request $request)
    {
        $data = $request->validate([
            'site_phone'       => 'nullable|string|max:50',
            'site_whatsapp'    => 'nullable|string|max:30',
            'site_email'       => 'nullable|email|max:191',
            'site_address'     => 'nullable|string|max:500',
            'social_facebook'  => 'nullable|url|max:300',
            'social_instagram' => 'nullable|url|max:300',
            'social_whatsapp'  => 'nullable|string|max:30',
            'social_tiktok'    => 'nullable|url|max:300',
            'social_x'         => 'nullable|url|max:300',
            'social_youtube'   => 'nullable|url|max:300',
            'shop_tax_rate'    => 'nullable|numeric|min:0|max:100',
            'shop_tax_type'    => 'nullable|in:percent,fixed',
            'notice_title'     => 'nullable|string|max:120',
            'notice_short'     => 'nullable|string|max:255',
            'notice_full'      => 'nullable|string|max:2000',
            'site_name'        => 'nullable|string|max:120',
            'site_name_ur'     => 'nullable|string|max:120',
            'site_website'     => 'nullable|string|max:120',
            'topbar_text'      => 'nullable|string|max:191',
            'topbar_location'  => 'nullable|string|max:191',
            'dispatch_postman_note'    => 'nullable|string|max:500',
            'dispatch_postman_note_ur' => 'nullable|string|max:500',
            'dispatch_logo_en'         => 'nullable|image|mimes:png,jpg,jpeg,webp,svg|max:1024',
            'dispatch_logo_ur'         => 'nullable|image|mimes:png,jpg,jpeg,webp,svg|max:1024',
            'dispatch_slip_lang'       => 'nullable|in:en,ur',
            'points_rupees_per_point'  => 'nullable|numeric|min:0',
            'points_per_review'        => 'nullable|integer|min:0',
            'points_value_rupees'      => 'nullable|numeric|min:0',
        ]);

        // Dispatch-slip logos: store uploaded files, keep the existing path when
        // no new file is sent (a checkbox can clear it).
        foreach (['dispatch_logo_en', 'dispatch_logo_ur'] as $key) {
            unset($data[$key]);
            if ($request->hasFile($key)) {
                $old = setting($key);
                if ($old && \Storage::disk('public')->exists($old)) {
                    \Storage::disk('public')->delete($old);
                }
                $data[$key] = $request->file($key)->store('dispatch-logos', 'public');
            } elseif ($request->boolean("remove_{$key}")) {
                $old = setting($key);
                if ($old && \Storage::disk('public')->exists($old)) {
                    \Storage::disk('public')->delete($old);
                }
                $data[$key] = '';
            }
        }

        Setting::putMany($data);

        return redirect()->route('admin.settings.index')->with('success', 'Website settings saved.');
    }

    /** Per-status customer message templates (#22). */
    public function updateStatusTemplates(Request $request)
    {
        $pairs = [];
        foreach (array_keys(config('order_flow.statuses', [])) as $key) {
            $field = 'status_msg_' . $key;
            $pairs[$field] = (string) $request->input($field, '');
        }
        Setting::putMany($pairs);

        return redirect()->route('admin.settings.index')->with('success', 'Order status messages saved.');
    }

    // ── Payment Methods ──

    public function storePaymentMethod(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:50|unique:payment_methods,name',
            'label' => 'required|string|max:50',
        ]);

        $maxOrder = PaymentMethod::max('sort_order') ?? 0;
        $validated['sort_order'] = $maxOrder + 1;

        PaymentMethod::create($validated);

        return redirect()->route('admin.settings.index')->with('success', 'Payment method added.');
    }

    public function updatePaymentMethod(Request $request, PaymentMethod $paymentMethod)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:50|unique:payment_methods,name,' . $paymentMethod->id,
            'label' => 'required|string|max:50',
            'show_on_website' => 'boolean',
            'is_cod'          => 'boolean',
            'account_title'   => 'nullable|string|max:191',
            'account_number'  => 'nullable|string|max:100',
            'bank_name'       => 'nullable|string|max:100',
            'instructions'    => 'nullable|string|max:500',
        ]);

        $validated['show_on_website'] = $request->boolean('show_on_website');
        $validated['is_cod']          = $request->boolean('is_cod');

        $paymentMethod->update($validated);

        return redirect()->route('admin.settings.index')->with('success', 'Payment method updated.');
    }

    public function togglePaymentMethod(PaymentMethod $paymentMethod)
    {
        $paymentMethod->update(['is_active' => !$paymentMethod->is_active]);

        return redirect()->route('admin.settings.index')->with('success', 'Payment method ' . ($paymentMethod->is_active ? 'enabled' : 'disabled') . '.');
    }

    public function destroyPaymentMethod(PaymentMethod $paymentMethod)
    {
        $paymentMethod->delete();

        return redirect()->route('admin.settings.index')->with('success', 'Payment method deleted.');
    }

    public function reorderPaymentMethods(Request $request)
    {
        $order = $request->input('order', []);
        foreach ($order as $index => $id) {
            PaymentMethod::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    // ── Dispatch Methods ──

    public function storeDispatchMethod(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:50|unique:dispatch_methods,name',
            'note'         => 'nullable|string|max:500',
            'has_tracking' => 'boolean',
        ]);

        $validated['has_tracking'] = $request->boolean('has_tracking');
        $maxOrder = DispatchMethod::max('sort_order') ?? 0;
        $validated['sort_order'] = $maxOrder + 1;

        DispatchMethod::create($validated);

        return redirect()->route('admin.settings.index')->with('success', 'Dispatch method added.');
    }

    public function updateDispatchMethod(Request $request, DispatchMethod $dispatchMethod)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:50|unique:dispatch_methods,name,' . $dispatchMethod->id,
            'note'         => 'nullable|string|max:500',
            'has_tracking' => 'boolean',
            'show_on_website' => 'boolean',
            'logo'         => 'nullable|image|mimes:png,jpg,jpeg,webp,svg|max:1024',
        ]);

        $validated['has_tracking']    = $request->boolean('has_tracking');
        $validated['show_on_website'] = $request->boolean('show_on_website');

        if ($request->hasFile('logo')) {
            if ($dispatchMethod->logo && \Storage::disk('public')->exists($dispatchMethod->logo)) {
                \Storage::disk('public')->delete($dispatchMethod->logo);
            }
            $validated['logo'] = $request->file('logo')->store('courier-logos', 'public');
        }

        $dispatchMethod->update($validated);

        return redirect()->route('admin.settings.index')->with('success', 'Dispatch method updated.');
    }

    public function toggleDispatchMethod(DispatchMethod $dispatchMethod)
    {
        $dispatchMethod->update(['is_active' => !$dispatchMethod->is_active]);

        return redirect()->route('admin.settings.index')->with('success', 'Dispatch method ' . ($dispatchMethod->is_active ? 'enabled' : 'disabled') . '.');
    }

    public function destroyDispatchMethod(DispatchMethod $dispatchMethod)
    {
        $dispatchMethod->delete();

        return redirect()->route('admin.settings.index')->with('success', 'Dispatch method deleted.');
    }

    // ── Delivery Charge Slabs ──

    public function storeDeliverySlab(Request $request)
    {
        $request->validate([
            'dispatch_method_id' => 'required|exists:dispatch_methods,id',
            'min_weight'         => 'required|numeric|min:0',
            'max_weight'         => 'required|numeric|gt:min_weight',
            'charge'             => 'required|numeric|min:0',
        ]);

        DeliveryChargeSlab::create($request->only('dispatch_method_id', 'min_weight', 'max_weight', 'charge'));

        return redirect()->route('admin.settings.index')->with('success', 'Delivery charge slab added.');
    }

    public function updateDeliverySlab(Request $request, DeliveryChargeSlab $slab)
    {
        $request->validate([
            'min_weight' => 'required|numeric|min:0',
            'max_weight' => 'required|numeric|gt:min_weight',
            'charge'     => 'required|numeric|min:0',
        ]);

        $slab->update($request->only('min_weight', 'max_weight', 'charge'));

        return redirect()->route('admin.settings.index')->with('success', 'Delivery charge slab updated.');
    }

    public function toggleDeliverySlab(DeliveryChargeSlab $slab)
    {
        $slab->update(['is_active' => !$slab->is_active]);

        return redirect()->route('admin.settings.index')->with('success', 'Delivery slab ' . ($slab->is_active ? 'enabled' : 'disabled') . '.');
    }

    public function destroyDeliverySlab(DeliveryChargeSlab $slab)
    {
        $slab->delete();

        return redirect()->route('admin.settings.index')->with('success', 'Delivery charge slab deleted.');
    }
}
