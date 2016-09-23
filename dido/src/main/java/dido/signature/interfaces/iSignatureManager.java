package dido.signature.interfaces;

import java.util.List;

import dido.signature.Signature;

public interface iSignatureManager {
	public boolean loadPDF(String path);
	public List<Signature> getSignatures();
}
